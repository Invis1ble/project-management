<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

interface MergeRequestFactoryInterface
{
    public function createMergeRequest(
        int $id,
        string $name,
        int $projectId,
        string $projectName,
        string $sourceBranchName,
        string $targetBranchName,
        string $status,
        string $guiUrl,
    ): MergeRequest;
}
