<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Repository;

use ReleaseManagement\ReleasePublication\Domain\Exception\ReleasePublicationNotFoundException;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;

interface ReleasePublicationRepositoryInterface
{
    /**
     * @throws ReleasePublicationNotFoundException
     */
    public function get(ReleasePublicationId $id): ReleasePublicationInterface;

    public function store(ReleasePublicationInterface $releasePublication): void;
}
