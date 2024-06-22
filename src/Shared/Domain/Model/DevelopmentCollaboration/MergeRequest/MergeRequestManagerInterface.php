<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

interface MergeRequestManagerInterface
{
    public function createMergeRequest(
        ProjectId $projectId,
        Title $title,
        Branch\Name $sourceBranchName,
        Branch\Name $targetBranchName,
    ): MergeRequest;

    public function mergeMergeRequest(
        ProjectId $projectId,
        MergeRequestIid $mergeRequestIid,
    ): MergeRequest;

    public function supports(ProjectId $projectId): bool;

    public function mergeRequest(
        ProjectId $projectId,
        MergeRequestIid $mergeRequestIid,
    ): MergeRequest;

    public function details(
        ProjectId $projectId,
        MergeRequestIid $mergeRequestIid,
    ): Details;
}
