<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Ui\Command\PublishRelease;

use Invis1ble\Messenger\Query\QueryBusInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\ProceedToNextStatus\ProceedToNextStatusCommand;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\PublishRelease as Application;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetLatestRelease\GetLatestReleaseQuery;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetLatestReleasePublication\GetLatestReleasePublicationQuery;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetReleasePublication\GetReleasePublicationQuery;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\AbstractReleasePublicationEvent;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublication;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Tag\MessageFactoryInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\Dictionary as PublicationStatusDictionary;
use Invis1ble\ProjectManagement\ReleasePublication\Ui\Command\ReleasePublicationAwareCommand;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Message;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Version;
use Invis1ble\ProjectManagement\Shared\Ui\Command\ShowingProgressCommandDispatcherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'pm:release:publish', description: 'Publishes a new release')]
final class PublishReleaseCommand extends ReleasePublicationAwareCommand
{
    public function __construct(
        QueryBusInterface $queryBus,
        Issue\GuiUrlFactoryInterface $issueGuiUrlFactory,
        ShowingProgressCommandDispatcherInterface $showingProgressCommandDispatcher,
        private readonly MessageFactoryInterface $tagMessageFactory,
        \DateInterval $pipelineMaxAwaitingTime,
    ) {
        parent::__construct(
            queryBus: $queryBus,
            issueGuiUrlFactory: $issueGuiUrlFactory,
            showingProgressCommandDispatcher: $showingProgressCommandDispatcher,
            pipelineMaxAwaitingTime: $pipelineMaxAwaitingTime,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->io->title('Publishing a new release');

        $resume = $input->getOption('resume');

        if (false === $resume) {
            $tagName = $this->newTagName();
            $preparedReleaseBranchName = $this->preparedReleaseBranchName();
            $tagMessage = $this->newTagMessage($preparedReleaseBranchName);
        } else {
            if (is_string($resume)) {
                $publicationId = ReleasePublicationId::fromString($resume);
                $publication = $this->getPublication(new GetReleasePublicationQuery($publicationId));
            } else {
                $publication = $this->getPublication(new GetLatestReleasePublicationQuery());
                $publicationId = $publication->id();
            }

            $preparedReleaseBranchName = $publication->branchName();
            $tagName = $publication->tagName();
            $tagMessage = $publication->tagMessage();
        }

        $this->io->section('Summary');

        $this->caption('Prepared release branch name');
        $this->io->text("<fg=bright-magenta;bg=black;options=bold> $preparedReleaseBranchName </>");

        $this->caption('New tag to create');
        $this->io->text("<fg=bright-magenta;bg=black;options=bold> $tagName </>");

        $this->caption('New tag message');
        $this->io->block((string) $tagMessage);

        if ($input->getOption('dry-run')) {
            return Command::SUCCESS;
        }

        $confirmed = $this->io->confirm('OK', false);

        if (!$confirmed) {
            $this->abort();
        }

        if (isset($publication)) {
            $status = (string) $publication->status();
        } else {
            $status = PublicationStatusDictionary::ReleaseCandidateCreated->value;
        }

        if (isset($publicationId)) {
            $command = new ProceedToNextStatusCommand($publicationId);
        } else {
            $command = new Application\PublishReleaseCommand(
                id: ReleasePublicationId::fromBranchName($preparedReleaseBranchName),
                tagName: $tagName,
                tagMessage: $tagMessage,
            );
        }

        $result = $this->showingProgressCommandDispatcher->dispatch(
            io: $this->io,
            command: $command,
            initialStatus: $status,
            finalStatus: PublicationStatusDictionary::Done,
            publicationClass: ReleasePublication::class,
            publicationEventClass: AbstractReleasePublicationEvent::class,
            publicationStatusDictionaryClass: PublicationStatusDictionary::class,
        );

        if (Command::SUCCESS === $result) {
            $this->io->success('Publication done');
        } else {
            $this->io->error('Unexpected error occurred');
        }

        return $result;
    }

    private function newTagMessage(Branch\Name $branchName): Message
    {
        $this->phase('Compiling new tag message...');

        $tagMessage = $this->tagMessageFactory->createReleasePublicationTagMessage($branchName);

        $this->caption('Tag message');
        $this->io->block((string) $tagMessage);

        return $this->io->ask(
            question: 'New tag message',
            default: (string) $tagMessage,
            validator: fn (string $tagMessage): Message => Message::fromString($tagMessage),
        );
    }

    private function preparedReleaseBranchName(): Branch\Name
    {
        $this->phase('Fetching latest release...');

        /** @var Version $release */
        $release = $this->queryBus->ask(new GetLatestReleaseQuery());

        if (null === $release) {
            throw new \UnexpectedValueException('No release found');
        }

        if ($release->released) {
            throw new \UnexpectedValueException("Latest release $release->name already released");
        }

        $latestReleaseBranchName = Branch\Name::fromString((string) $release->name);

        $this->caption("Latest release branch name: $latestReleaseBranchName");

        return $latestReleaseBranchName;
    }
}
