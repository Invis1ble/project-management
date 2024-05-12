<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model;

use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;

interface ReleasePublicationFactoryInterface
{
    public function createReleasePublication(Name $branchName): ReleasePublicationInterface;
}
