<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Ui\Command\PublishHotfix;

use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Query\QueryBusInterface;
use ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication\CreateHotfixPublicationCommand;
use ProjectManagement\HotfixPublication\Application\UseCase\Query\GetReadyForPublishHotfixesInActiveSprint\GetReadyForPublishHotfixesInActiveSprintQuery;
use ProjectManagement\HotfixPublication\Domain\Model\SourceCodeRepository\Tag\MessageFactoryInterface;
use ProjectManagement\Shared\Application\UseCase\Query\GetLatestTagToday\GetLatestTagTodayQuery;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Message;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Tag;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\VersionName;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\GuiUrlFactoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use ProjectManagement\Shared\Ui\Command\IssuesAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'pm:hotfix:publish', description: 'Publish hotfixes')]
final class PublishHotfixCommand extends IssuesAwareCommand
{
    public function __construct(
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus,
        GuiUrlFactoryInterface $issueGuiUrlFactory,
        private readonly MessageFactoryInterface $tagMessageFactory,
    ) {
        parent::__construct($queryBus, $commandBus, $issueGuiUrlFactory);
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
        $hotfixes = $this->enrichIssuesWithMergeRequests($hotfixes);

        $this->io->section('Summary');

        $this->caption('New tag to create');
        $this->io->block((string) $tagName);

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

        return Command::SUCCESS;
    }

    private function newTagName(): VersionName
    {
        $this->phase('Calculating new tag today...');

        /** @var ?Tag<VersionName> $latestTagToday */
        $latestTagToday = $this->queryBus->ask(new GetLatestTagTodayQuery());

        $this->caption('Latest tag today');

        if (null === $latestTagToday) {
            $this->io->block('No tags today');
        } else {
            $this->io->block((string) $latestTagToday->name);
        }

        $this->caption('New calculated tag');

        if (null === $latestTagToday) {
            $tagName = VersionName::create();
        } else {
            $tagName = VersionName::fromRef($latestTagToday->name)->bumpVersion();
        }

        $this->io->block((string) $tagName);

        return $this->io->ask(
            question: 'New tag',
            default: (string) $tagName,
            validator: function (string $answer) use ($latestTagToday): VersionName {
                $tagName = VersionName::fromString($answer);

                if (null === $latestTagToday) {
                    return $tagName;
                }

                if (!$tagName->versionNewerThan(VersionName::fromRef($latestTagToday->name))) {
                    throw new \InvalidArgumentException("Provided version must be greater than latest tag $latestTagToday->name version");
                }

                return $tagName;
            },
        );
    }

    private function newTagMessage(IssueList $hotfixes): Message
    {
        $this->phase('Compiling new tag message...');

        $tagMessage = $this->tagMessageFactory->createHotfixPublicationTagMessage($hotfixes);

        $this->caption('Tag message');
        $this->io->block((string) $tagMessage);

        return $this->io->ask(
            question: 'New tag message',
            default: (string) $tagMessage,
            validator: fn (string $tagMessage): Message => Message::fromString($tagMessage),
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
