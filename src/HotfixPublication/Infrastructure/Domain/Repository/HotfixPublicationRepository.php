<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Infrastructure\Domain\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Exception\HotfixPublicationNotFoundException;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublication;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Repository\EventDispatchingRepository;

final class HotfixPublicationRepository extends EventDispatchingRepository implements HotfixPublicationRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        EventBusInterface $eventBus,
    ) {
        parent::__construct($registry, HotfixPublication::class, $eventBus);
    }

    public function get(HotfixPublicationId $id): HotfixPublicationInterface
    {
        $hotfixPublication = $this->find($id);

        if (null === $hotfixPublication) {
            throw new HotfixPublicationNotFoundException($id);
        }

        return $hotfixPublication;
    }

    public function store(HotfixPublicationInterface $hotfixPublication): void
    {
        $this->persist($hotfixPublication);
    }
}