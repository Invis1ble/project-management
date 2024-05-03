<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model;

interface SourceCodeRepositoryInterface
{
    public function createBranch(BranchName $name): void;
}
