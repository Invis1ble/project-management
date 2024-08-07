<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Ui\Command\PublishHotfix;

use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Query\QueryBusInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication\CreateHotfixPublicationCommand;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetLatestHotfixPublicationByTag\GetLatestHotfixPublicationByTagQuery;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetReadyForPublishHotfixesInActiveSprint\GetReadyForPublishHotfixesInActiveSprintQuery;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\SourceCodeRepository\Tag\MessageFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\GuiUrlFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use Invis1ble\ProjectManagement\Shared\Ui\Command\IssuesAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(name: 'pm:hotfix:publish', description: 'Publish hotfixes')]
final class PublishHotfixCommand extends IssuesAwareCommand
{
    public function __construct(
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus,
        GuiUrlFactoryInterface $issueGuiUrlFactory,
        SerializerInterface $serializer,
        \DateInterval $pipelineMaxAwaitingTime,
        private readonly MessageFactoryInterface $tagMessageFactory,
    ) {
        parent::__construct(
            queryBus: $queryBus,
            commandBus: $commandBus,
            issueGuiUrlFactory: $issueGuiUrlFactory,
            serializer: $serializer,
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

        $keys = $input->getArgument('key');

        $hotfixes = $this->readyForPublishHotfixes($keys);

        if ($hotfixes->empty()) {
            return Command::SUCCESS;
        }

        $tagName = $this->newTagName();
        $tagMessage = $this->newTagMessage($hotfixes);
        $hotfixes = $this->enrichIssuesWithMergeRequests(
            issues: $hotfixes,
            targetBranchName: Branch\Name::fromString('master'),
        );

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

        $confirmed = $this->io->confirm('OK', !empty($keys));

        if (!$confirmed) {
            $this->abort();
        }

        $this->commandBus->dispatch(new CreateHotfixPublicationCommand(
            tagName: $tagName,
            tagMessage: $tagMessage,
            hotfixes: $hotfixes,
        ));

        return $this->showProgressLog(
            query: new GetLatestHotfixPublicationByTagQuery($tagName),
            inFinalState: fn (HotfixPublicationInterface $publication): bool => $publication->published(),
        );
    }

    private function newTagMessage(IssueList $hotfixes): Tag\Message
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
     * @param string[] $keys
     */
    private function readyForPublishHotfixes(array $keys): IssueList
    {
        $this->phase('Fetching Ready for Publish hotfixes...');

        /** @var IssueList $issues */
        $issues = $this->queryBus->ask(new GetReadyForPublishHotfixesInActiveSprintQuery(...array_map(
            fn (string $key): Key => Key::fromString($key),
            $keys,
        )));

        if ($issues->empty()) {
            $text = 'No Ready for Publish hotfixes found in the active sprint';

            if (!empty($keys)) {
                $text .= ' within provided keys';
            }

            $this->caption($text);

            return $issues;
        }

        if (empty($keys)) {
            $this->listIssues($issues);

            $issues = $issues->filter(
                fn (Issue $issue): bool => $this->io->confirm("Add $issue->key to the publication"),
            );
        }

        $this->caption('Hotfixes to publish');

        $this->listIssues($issues);

        return $issues;
    }
}
