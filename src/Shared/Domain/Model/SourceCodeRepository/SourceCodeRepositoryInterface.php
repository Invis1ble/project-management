<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\SourceCodeRepository;

use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

interface SourceCodeRepositoryInterface
{
    public function createBranch(Name $name): void;
}
