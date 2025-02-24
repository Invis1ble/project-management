<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Ui\Command\PublishHotfix;

use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Query\QueryBusInterface;
use Invis1ble\Messenger\Query\QueryInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication\CreateHotfixPublicationCommand;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\ProceedToNextStatus\ProceedToNextStatusCommand;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetHotfixesInActiveSprint\GetHotfixesInActiveSprintQuery;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetHotfixPublication\GetHotfixPublicationQuery;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetLatestHotfixPublication\GetLatestHotfixPublicationQuery;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\AbstractHotfixPublicationEvent;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Exception\HotfixPublicationNotFoundException;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublication;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\SourceCodeRepository\Tag\MessageFactoryInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Ui\Command\PublicationProgressFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\EventNameReducerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Ui\Command\PublicationAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(name: 'pm:hotfix:publish', description: 'Publish hotfixes')]
final class PublishHotfixCommand extends PublicationAwareCommand
{
    private readonly Issue\Status $statusReadyForPublish;

    public function __construct(
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus,
        Issue\GuiUrlFactoryInterface $issueGuiUrlFactory,
        SerializerInterface $serializer,
        Issue\StatusProviderInterface $issueStatusProvider,
        \DateInterval $pipelineMaxAwaitingTime,
        private readonly MessageFactoryInterface $tagMessageFactory,
        private readonly HubInterface $mercureHub,
        private readonly EventNameReducerInterface $eventNameReducer,
        private readonly PublicationProgressFactoryInterface $publicationProgressFactory,
    ) {
        $this->statusReadyForPublish = $issueStatusProvider->readyForPublish();

        parent::__construct(
            queryBus: $queryBus,
            commandBus: $commandBus,
            serializer: $serializer,
            issueStatusProvider: $issueStatusProvider,
            issueGuiUrlFactory: $issueGuiUrlFactory,
            pipelineMaxAwaitingTime: $pipelineMaxAwaitingTime,
        );
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addArgument(
                name: 'key',
                mode: InputArgument::IS_ARRAY,
                description: 'What do you want to publish (separate multiple hotfixes with a space)?',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->io->title('Publishing hotfixes');

        $resume = $input->getOption('resume');

        if (false === $resume) {
            $keys = $input->getArgument('key');

            $hotfixes = $this->readyForPublishHotfixes(empty($keys) ? null : $keys);

            if ($hotfixes->empty()) {
                return Command::SUCCESS;
            }

            $tagName = $this->newTagName();
            $tagMessage = $this->newTagMessage($hotfixes);
            $hotfixes = $this->enrichIssuesWithMergeRequests(
                issues: $hotfixes,
                targetBranchName: Branch\Name::fromString('master'),
            );
        } else {
            if (is_string($resume)) {
                $publicationId = HotfixPublicationId::fromString($resume);
                $publication = $this->getPublication(new GetHotfixPublicationQuery($publicationId));
            } else {
                $publication = $this->getPublication(new GetLatestHotfixPublicationQuery());
                $publicationId = $publication->id();
            }

            $tagName = $publication->tagName();
            $tagMessage = $publication->tagMessage();
            $hotfixes = $publication->hotfixes();
        }

        $this->io->section('Summary');

        $this->caption('New tag to create');
        $this->io->text("<fg=bright-magenta;bg=black;options=bold> $tagName </>");

        $this->caption('New tag message');
        $this->io->block((string) $tagMessage);

        $this->caption('Hotfixes to publish');
        $this->listIssues($hotfixes);

        $mergeRequestsToMerge = $hotfixes->mergeRequestsToMerge();

        if ($mergeRequestsToMerge->empty()) {
            $this->caption('No Merge requests to merge');
        } else {
            $this->caption('Merge requests to merge');
            $this->listMergeRequests($hotfixes->mergeRequestsToMerge());
        }

        if ($input->getOption('dry-run')) {
            return Command::SUCCESS;
        }

        $confirmed = $this->io->confirm('OK', !empty($keys) || is_string($resume));

        if (!$confirmed) {
            $this->abort();
        }

        if (isset($publication)) {
            $status = (string) $publication->status();
        } else {
            $status = 'inited';
        }

        $publicationProgress = $this->publicationProgressFactory->create(
            io: $this->io,
            dateTimeFormat: 'd.m.Y H:i:sP',
        );

        $publicationProgress->start(status: $status);

        $topics = ['/api/events'];

        $url = $this->mercureHub->getPublicUrl();

        if (null !== $topics) {
            // We cannot use http_build_query() because this method doesn't support generating multiple query parameters with the same name without the [] suffix
            $separator = '?';
            foreach ((array) $topics as $topic) {
                $url .= $separator . 'topic=' . rawurlencode($topic);
                if ('?' === $separator) {
                    $separator = '&';
                }
            }
        }

        $client = HttpClient::create();
        $client = new EventSourceHttpClient($client, 10);
        $source = $client->connect($url);

        $untilTime = (new \DateTimeImmutable())->add($this->pipelineMaxAwaitingTime);

        $result = Command::FAILURE;

        if (isset($publicationId)) {
            $this->commandBus->dispatch(new ProceedToNextStatusCommand($publicationId));
        } else {
            $this->commandBus->dispatch(new CreateHotfixPublicationCommand(
                tagName: $tagName,
                tagMessage: $tagMessage,
                hotfixes: $hotfixes,
            ));
        }

        while ($source) {
            foreach ($client->stream($source, 0.1) as $chunk) {
                if ($chunk->isTimeout()) {
                    continue;
                }

                if (new \DateTimeImmutable() > $untilTime) {
                    $publicationProgress->setStatus('stuck in ' . $status);

                    break 2;
                }

                if ($chunk->isLast()) {
                    break 2;
                }

                if ($chunk instanceof ServerSentEvent) {
                    $data = $chunk->getArrayData();
                    $eventName = $this->eventNameReducer->expand($data['name']);

                    if (is_subclass_of($eventName, AbstractHotfixPublicationEvent::class)) {
                        $untilTime = (new \DateTimeImmutable())->add($this->pipelineMaxAwaitingTime);

                        /** @var HotfixPublication $publication */
                        $publication = $this->serializer->denormalize($data['context'], HotfixPublication::class);

                        $publicationProgress->setStatus((string) $publication->status());
                        $publicationProgress->advance();

                        if ($publication->published()) {
                            $publicationProgress->finish();

                            $result = Command::SUCCESS;

                            break 2;
                        }
                    } else {
                        $event = $this->serializer->denormalize($data['context'], $eventName);
                        $publicationProgress->addEvent($event);
                    }
                }
            }
        }

        $this->io->newLine();

        if (Command::SUCCESS === $result) {
            $this->io->success('Publication done');
        } else {
            $this->io->error('Unexpected error occurred');
        }

        return $result;
    }

    protected function getPublication(QueryInterface $query): HotfixPublicationInterface
    {
        $startTime = new \DateTimeImmutable();
        $untilTime = $startTime->add($this->pipelineMaxAwaitingTime);

        $getPublicationMaxTries = 3;
        $retryCounter = 0;

        while (new \DateTimeImmutable() <= $untilTime) {
            try {
                return $this->queryBus->ask($query);
            } catch (HotfixPublicationNotFoundException $exception) {
                // publication is not created, await async handlers
                sleep(3);
                ++$retryCounter;

                if ($retryCounter >= $getPublicationMaxTries) {
                    throw $exception;
                }

                continue;
            }
        }

        throw new HotfixPublicationNotFoundException();
    }

    private function newTagMessage(Issue\IssueList $hotfixes): Tag\Message
    {
        $this->phase('Compiling new tag message...');

        $tagMessage = $this->tagMessageFactory->createHotfixPublicationTagMessage($hotfixes);

        $this->caption('Tag message');
        $this->io->block((string) $tagMessage);

        return $this->io->ask(
            question: 'New tag message',
            default: (string) $tagMessage,
            validator: fn (string $tagMessage): Tag\Message => Tag\Message::fromString($tagMessage),
        );
    }

    /**
     * @param string[]|null $keys
     */
    private function readyForPublishHotfixes(?array $keys): Issue\IssueList
    {
        $this->phase("Fetching $this->statusReadyForPublish hotfixes...");

        /** @var Issue\IssueList $issues */
        $issues = $this->queryBus->ask(new GetHotfixesInActiveSprintQuery(
            keys: null === $keys ? null : array_map(
                fn (string $key): Issue\Key => Issue\Key::fromString($key),
                $keys,
            ),
            statuses: [$this->statusReadyForPublish],
        ));

        if ($issues->empty()) {
            $text = "No $this->statusReadyForPublish hotfixes found in the active sprint";

            if (!empty($keys)) {
                $text .= ' within provided keys';
            }

            $this->caption($text);

            return $issues;
        }

        if (empty($keys)) {
            $this->listIssues($issues);

            $issues = $issues->filter(
                fn (Issue\Issue $issue): bool => $this->io->confirm("Add $issue->key to the publication"),
            );
        }

        $this->caption('Hotfixes to publish');
        $this->listIssues($issues);

        return $issues;
    }
}
