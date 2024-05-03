<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Repository;

use ReleaseManagement\Release\Domain\Exception\ReleaseNotFoundException;
use ReleaseManagement\Release\Domain\Model\ReleaseId;
use ReleaseManagement\Release\Domain\Model\ReleaseInterface;

interface ReleaseRepositoryInterface
{
    /**
     * @throws ReleaseNotFoundException
     */
    public function get(ReleaseId $id): ReleaseInterface;

    public function store(ReleaseInterface $release): void;
}
