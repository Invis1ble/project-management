<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\fixture\CreateMergeRequestTrait;
use Psr\Http\Message\UriFactoryInterface;

trait CreateMergeRequestsTrait
{
    use CreateMergeRequestTrait;

    public function createMergeRequests(
        UriFactoryInterface $uriFactory,
        int $iid = 4,
        int $backendProjectId = 1,
        int $frontendProjectId = 2,
        string $projectName = 'my-group/my-project',
        string $title = 'Close PROJECT-2 Fix terrible bug',
        string $sourceBranchName = 'PROJECT-2',
        string $targetBranchName = 'master',
        MergeRequest\Status $jiraStatus = MergeRequest\Status::Open,
        MergeRequest\Details\Status\Dictionary $gitlabStatus = MergeRequest\Details\Status\Dictionary::Mergeable,
    ): MergeRequest\MergeRequestList {
        return new MergeRequest\MergeRequestList(
            $this->createMergeRequest(
                uriFactory: $uriFactory,
                iid: $iid,
                projectId: $backendProjectId,
                projectName: $projectName,
                title: $title,
                sourceBranchName: $sourceBranchName,
                targetBranchName: $targetBranchName,
                jiraStatus: $jiraStatus,
                gitlabStatus: $gitlabStatus,
            ),
            $this->createMergeRequest(
                uriFactory: $uriFactory,
                iid: $iid,
                projectId: $frontendProjectId,
                projectName: $projectName,
                title: $title,
                sourceBranchName: $sourceBranchName,
                targetBranchName: $targetBranchName,
                jiraStatus: $jiraStatus,
                gitlabStatus: $gitlabStatus,
            ),
        );
    }
}
