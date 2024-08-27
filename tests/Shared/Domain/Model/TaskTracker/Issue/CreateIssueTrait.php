<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Board\BoardId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\CreateMergeRequestsTrait;
use Psr\Http\Message\UriFactoryInterface;

trait CreateIssueTrait
{
    use CreateMergeRequestsTrait;

    public function createIssue(
        UriFactoryInterface $uriFactory,
        string $key = 'PROJECT-2',
        int $backendProjectId = 1,
        int $frontendProjectId = 2,
        string $projectName = 'my-group/my-project',
        string $summary = 'Fix terrible bug',
        int $mergeRequestIid = 4,
        string $mergeRequestTargetBranchName = 'master',
        MergeRequest\Status $jiraStatus = MergeRequest\Status::Open,
        MergeRequest\Details\Status\Dictionary $gitlabStatus = MergeRequest\Details\Status\Dictionary::Mergeable,
        Sprint\State $sprintState = Sprint\State::Active,
    ): Issue\Issue {
        $mergeRequests = $this->createMergeRequests(
            uriFactory: $uriFactory,
            iid: 45,
            backendProjectId: $backendProjectId,
            frontendProjectId: $frontendProjectId,
            projectName: $projectName,
            title: 'Update issue branch',
            sourceBranchName: 'master',
            targetBranchName: 'PROJECT-2',
            jiraStatus: MergeRequest\Status::Merged,
            gitlabStatus: MergeRequest\Details\Status\Dictionary::NotOpen,
        );

        $mergeRequestsToMerge = $this->createMergeRequests(
            uriFactory: $uriFactory,
            iid: $mergeRequestIid,
            backendProjectId: $backendProjectId,
            frontendProjectId: $frontendProjectId,
            projectName: $projectName,
            title: "Close $key $summary",
            sourceBranchName: $key,
            targetBranchName: $mergeRequestTargetBranchName,
            jiraStatus: $jiraStatus,
            gitlabStatus: $gitlabStatus,
        );

        return new Issue\Issue(
            id: Issue\IssueId::from(1),
            key: Issue\Key::fromString($key),
            typeId: Issue\TypeId::fromString('3'),
            subtask: false,
            summary: Issue\Summary::fromString($summary),
            sprints: new Sprint\SprintList(
                new Sprint\Sprint(
                    boardId: BoardId::from(42),
                    name: Sprint\Name::fromString('June 2024 1-2'),
                    state: $sprintState,
                ),
            ),
            mergeRequests: $mergeRequests,
            mergeRequestsToMerge: $mergeRequestsToMerge,
        );
    }
}
