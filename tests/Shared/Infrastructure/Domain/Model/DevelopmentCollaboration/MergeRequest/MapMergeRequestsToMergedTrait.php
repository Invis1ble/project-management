<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

trait MapMergeRequestsToMergedTrait
{
    public function mapMergeRequestsToMerged(MergeRequest\MergeRequestList $mergeRequests): MergeRequest\MergeRequestList
    {
        return new MergeRequest\MergeRequestList(...$mergeRequests->map(
            fn (MergeRequest\MergeRequest $mr): MergeRequest\MergeRequest => new MergeRequest\MergeRequest(
                id: $mr->id,
                title: $mr->title,
                projectId: $mr->projectId,
                projectName: $mr->projectName,
                sourceBranchName: $mr->sourceBranchName,
                targetBranchName: $mr->targetBranchName,
                status: MergeRequest\Status::Merged,
                guiUrl: $mr->guiUrl,
                details: new MergeRequest\Details\Details(
                    status: new MergeRequest\Details\Status\StatusNotOpen(),
                ),
            ),
        ));
    }
}
