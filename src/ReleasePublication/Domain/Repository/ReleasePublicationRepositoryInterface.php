<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Repository;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Exception\ReleasePublicationNotFoundException;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;

interface ReleasePublicationRepositoryInterface
{
    /**
     * @throws ReleasePublicationNotFoundException
     */
    public function get(ReleasePublicationId $id): ReleasePublicationInterface;

    public function store(ReleasePublicationInterface $releasePublication): void;
}
