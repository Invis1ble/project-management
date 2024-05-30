<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Repository;

use ProjectManagement\HotfixPublication\Domain\Exception\HotfixPublicationNotFoundException;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;

interface HotfixPublicationRepositoryInterface
{
    /**
     * @throws HotfixPublicationNotFoundException
     */
    public function get(HotfixPublicationId $id): HotfixPublicationInterface;

    public function store(HotfixPublicationInterface $hotfixPublication): void;
}
