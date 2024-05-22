<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Repository;

use ProjectManagement\ReleasePublication\Domain\Exception\ReleasePublicationNotFoundException;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;

interface ReleasePublicationRepositoryInterface
{
    /**
     * @throws ReleasePublicationNotFoundException
     */
    public function get(ReleasePublicationId $id): ReleasePublicationInterface;

    public function store(ReleasePublicationInterface $releasePublication): void;
}
