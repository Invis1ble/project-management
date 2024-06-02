<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

interface BranchFactoryInterface
{
    public function createBranch(
        string $name,
        bool $protected,
        string $guiUrl,
        string $commitId,
        ?string $commitMessage,
        string $commitCreatedAt,
    ): Branch;
}
