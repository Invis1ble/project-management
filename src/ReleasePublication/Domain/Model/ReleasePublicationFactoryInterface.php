<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model;

use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;

interface ReleasePublicationFactoryInterface
{
    public function createReleasePublication(Name $branchName): ReleasePublicationInterface;
}
