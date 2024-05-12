<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Infrastructure\Domain\Model;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationFactoryInterface;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\ReleasePublication\Infrastructure\Domain\Model\Entity\ReleasePublication;

final readonly class ReleasePublicationFactory implements ReleasePublicationFactoryInterface
{
    public function createReleasePublication(Name $branchName): ReleasePublicationInterface
    {
        return ReleasePublication::create($branchName);
    }
}
