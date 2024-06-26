<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

interface MergeRequestFactoryInterface
{
    public function createMergeRequest(
        int $iid,
        string $title,
        int $projectId,
        string $projectName,
        string $sourceBranchName,
        string $targetBranchName,
        ?string $status,
        string $guiUrl,
        ?string $detailedMergeStatus,
    ): MergeRequest;
}
