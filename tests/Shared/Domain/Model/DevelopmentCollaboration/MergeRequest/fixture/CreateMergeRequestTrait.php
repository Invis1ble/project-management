<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\fixture;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Psr\Http\Message\UriFactoryInterface;

trait CreateMergeRequestTrait
{
    public function createMergeRequest(
        UriFactoryInterface $uriFactory,
        int $iid = 1,
        int $projectId = 1,
        string $projectName = 'my-group/my-project',
        string $title = 'Close PROJECT-1 Fix terrible bug',
        string $sourceBranchName = 'PROJECT-1',
        string $targetBranchName = 'master',
        MergeRequest\Status $jiraStatus = MergeRequest\Status::Open,
        MergeRequest\Details\Status\Dictionary $gitlabStatus = MergeRequest\Details\Status\Dictionary::Mergeable,
    ): MergeRequest\MergeRequest {
        $mergeRequestIid = MergeRequest\MergeRequestIid::from($iid);

        return new MergeRequest\MergeRequest(
            iid: $mergeRequestIid,
            title: MergeRequest\Title::fromString($title),
            projectId: Project\ProjectId::from($projectId),
            projectName: Project\Name::fromString($projectName),
            sourceBranchName: Branch\Name::fromString($sourceBranchName),
            targetBranchName: Branch\Name::fromString($targetBranchName),
            status: $jiraStatus,
            guiUrl: $uriFactory->createUri("https://gitlab.example.com/$projectName/-/merge_requests/$mergeRequestIid"),
            details: new MergeRequest\Details\Details(
                status: MergeRequest\Details\Status\StatusFactory::createStatus($gitlabStatus),
            ),
        );
    }
}
