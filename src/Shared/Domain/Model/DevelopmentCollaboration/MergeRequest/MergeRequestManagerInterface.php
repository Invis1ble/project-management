<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

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
        MergeRequestId $mergeRequestId,
    ): MergeRequest;

    public function supports(ProjectId $projectId): bool;

    public function details(
        ProjectId $projectId,
        MergeRequestId $mergeRequestId,
    ): Details;
}
