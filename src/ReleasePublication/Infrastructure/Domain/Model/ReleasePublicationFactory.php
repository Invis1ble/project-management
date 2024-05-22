<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Infrastructure\Domain\Model;

use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublication;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationFactoryInterface;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;

final readonly class ReleasePublicationFactory implements ReleasePublicationFactoryInterface
{
    public function createReleasePublication(Name $branchName): ReleasePublicationInterface
    {
        return ReleasePublication::create($branchName);
    }
}
