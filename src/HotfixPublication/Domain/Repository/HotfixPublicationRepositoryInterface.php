<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Exception\HotfixPublicationNotFoundException;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;

interface HotfixPublicationRepositoryInterface
{
    /**
     * @throws HotfixPublicationNotFoundException
     */
    public function get(HotfixPublicationId $id): HotfixPublicationInterface;

    public function store(HotfixPublicationInterface $hotfixPublication): void;
}
