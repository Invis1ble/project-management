<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint;
use Psr\Http\Message\UriFactoryInterface;

trait CreateIssuesTrait
{
    use CreateIssueTrait;

    public function createIssues(
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
    ): Issue\IssueList {
        return new Issue\IssueList($this->createIssue(
            uriFactory: $uriFactory,
            key: $key,
            backendProjectId: $backendProjectId,
            frontendProjectId: $frontendProjectId,
            projectName: $projectName,
            summary: $summary,
            mergeRequestIid: $mergeRequestIid,
            mergeRequestTargetBranchName: $mergeRequestTargetBranchName,
            jiraStatus: $jiraStatus,
            gitlabStatus: $gitlabStatus,
            sprintState: $sprintState,
        ));
    }
}
