<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Ui\Command\PrepareRelease;

use ProjectManagement\ReleasePublication\Application\UseCase\Command\CreateReleasePublication\CreateReleasePublicationCommand;
use ProjectManagement\ReleasePublication\Application\UseCase\Query\GetLatestRelease\GetLatestReleaseQuery;
use ProjectManagement\ReleasePublication\Application\UseCase\Query\GetReadyToMergeTasksInActiveSprint\GetReadyToMergeTasksInActiveSprintQuery;
use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Version;
use ProjectManagement\Shared\Ui\Command\IssuesAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'pm:release:prepare', description: 'Prepares a new release')]
final class PrepareReleaseCommand extends IssuesAwareCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Preparing new release');

        $newReleaseBranchName = $this->newReleaseBranchName();
        $tasks = $this->readyToMergeTasks();
        $tasks = $this->enrichIssuesWithMergeRequests($tasks);

        $this->io->section('Summary');

        $this->caption('New release branch name');
        $this->io->block((string) $newReleaseBranchName);

        $this->caption('Ready to Merge tasks in the active sprint');
        $this->listIssues($tasks);

        if ($tasks->empty()) {
            $this->caption('No Ready to Merge tasks in the active sprint');

            return Command::SUCCESS;
        }

        $mergeRequestsToMerge = $tasks->mergeRequestsToMerge();

        if ($mergeRequestsToMerge->empty()) {
            $this->caption('No Merge requests will be merged');
        } else {
            $this->caption('Merge requests will be merged');
            $this->listMergeRequests($tasks->mergeRequestsToMerge());
        }

        $confirmed = $this->io->confirm('OK', false);

        if (!$confirmed) {
            $this->abort();
        }

        $this->commandBus->dispatch(new CreateReleasePublicationCommand(
            branchName: $newReleaseBranchName,
            readyToMergeTasks: $tasks,
        ));

        return Command::SUCCESS;
    }

    private function newReleaseBranchName(): Name
    {
        $this->phase('Fetching latest release...');

        /** @var Version $release */
        $release = $this->queryBus->ask(new GetLatestReleaseQuery());

        if (null === $release) {
            throw new \UnexpectedValueException('No release found');
        }

        if (!$release->released) {
            throw new \UnexpectedValueException("Latest release $release->name not released yet");
        }

        $latestReleaseBranchName = Name::fromString((string) $release->name);

        $this->caption("Latest release branch name: $latestReleaseBranchName");

        return $this->io->ask(
            question: 'New release branch name',
            default: (string) $latestReleaseBranchName->bumpVersion(),
            validator: function (string $branchName) use ($latestReleaseBranchName): Name {
                $branchName = Name::fromString($branchName);

                if (!$branchName->versionNewerThan($latestReleaseBranchName)) {
                    throw new \InvalidArgumentException("Provided version must be greater than latest release $latestReleaseBranchName version");
                }

                return $branchName;
            },
        );
    }

    private function readyToMergeTasks(): IssueList
    {
        $this->phase('Fetching Ready to Merge tasks...');

        /** @var IssueList $tasks */
        $tasks = $this->queryBus->ask(new GetReadyToMergeTasksInActiveSprintQuery());

        if ($tasks->empty()) {
            $this->caption('No Ready to Merge tasks found in the active sprint.');

            return $tasks;
        }

        $this->caption('Ready to Merge tasks');

        $this->listIssues($tasks);

        $tasks = $tasks->filter(
            fn (Issue $task): bool => $this->io->confirm("Add $task->key to the new release"),
        );

        $this->caption('Tasks to merge');

        $this->listIssues($tasks);

        return $tasks;
    }
}
