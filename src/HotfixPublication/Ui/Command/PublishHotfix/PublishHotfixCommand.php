<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Ui\Command\PublishHotfix;

use ProjectManagement\HotfixPublication\Application\UseCase\Query\GetReadyForPublishHotfixesInActiveSprint\GetReadyForPublishHotfixesInActiveSprintQuery;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use ProjectManagement\Shared\Ui\Command\IssuesAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'hotfix:publish', description: 'Publish hotfixes')]
final class PublishHotfixCommand extends IssuesAwareCommand
{
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
        $hotfixes = $this->enrichIssuesWithMergeRequests($hotfixes);

        $this->io->section('Summary');

        if ($hotfixes->empty()) {
            $this->caption('No Ready for Publish Hotfixes in the active sprint');

            return Command::SUCCESS;
        }

        $this->caption('Ready for Publish Hotfixes in the active sprint');
        $this->listIssues($hotfixes);

        $mergeRequestsToMerge = $hotfixes->mergeRequestsToMerge();

        if ($mergeRequestsToMerge->empty()) {
            $this->caption('No Merge requests will be merged');
        } else {
            $this->caption('Merge requests that will be merged');
            $this->listMergeRequests($hotfixes->mergeRequestsToMerge());
        }

        if ($input->getOption('dry-run')) {
            return Command::SUCCESS;
        }

        $confirmed = $this->io->confirm('OK', false);

        if (!$confirmed) {
            $this->abort();
        }

        // $this->commandBus->dispatch(new CreateHotfixPublicationCommand(
        //    branchName: $newReleaseBranchName,
        //    readyToMergeTasks: $tasks,
        // ));

        return Command::SUCCESS;
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
            $this->caption('No Ready for Publish hotfixes found in the active sprint');

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
