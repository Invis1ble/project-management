<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Ui\Command\PrepareRelease;

use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\CreateReleasePublication\CreateReleasePublicationCommand;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetLatestRelease\GetLatestReleaseQuery;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetReadyToMergeTasksInActiveSprint\GetReadyToMergeTasksInActiveSprintQuery;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name as BasicBranchName;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Version;
use Invis1ble\ProjectManagement\Shared\Ui\Command\IssuesAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'pm:release:prepare', description: 'Prepares a new release')]
final class PrepareReleaseCommand extends IssuesAwareCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->io->title('Preparing a new release');

        $newReleaseBranchName = $this->newReleaseBranchName();
        $tasks = $this->readyToMergeTasks();
        $tasks = $this->enrichIssuesWithMergeRequests(
            issues: $tasks,
            targetBranchName: BasicBranchName::fromString('develop'),
        );

        $this->io->section('Summary');

        $this->caption('New release branch name');
        $this->io->text("<fg=bright-magenta;bg=black;options=bold> $newReleaseBranchName </>");

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

        if ($input->getOption('dry-run')) {
            return Command::SUCCESS;
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

    private function newReleaseBranchName(): Branch\Name
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

        $latestReleaseBranchName = Branch\Name::fromString((string) $release->name);

        $this->caption("Latest release branch name: $latestReleaseBranchName");

        return $this->io->ask(
            question: 'New release branch name',
            default: (string) $latestReleaseBranchName->bumpVersion(),
            validator: function (string $branchName) use ($latestReleaseBranchName): Branch\Name {
                $branchName = Branch\Name::fromString($branchName);

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
