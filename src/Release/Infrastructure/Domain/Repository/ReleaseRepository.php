<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Infrastructure\Domain\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Invis1ble\Messenger\Event\EventBusInterface;
use ReleaseManagement\Release\Domain\Exception\ReleaseNotFoundException;
use ReleaseManagement\Release\Domain\Model\ReleaseId;
use ReleaseManagement\Release\Domain\Model\ReleaseInterface;
use ReleaseManagement\Release\Domain\Repository\ReleaseRepositoryInterface;
use ReleaseManagement\Release\Infrastructure\Domain\Model\Entity\Release;
use ReleaseManagement\Shared\Domain\Repository\EventDispatchingRepository;

final class ReleaseRepository extends EventDispatchingRepository implements ReleaseRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        EventBusInterface $eventBus,
    ) {
        parent::__construct($registry, Release::class, $eventBus);
    }

    /**
     * {@inheritdoc}
     */
    public function get(ReleaseId $id): ReleaseInterface
    {
        $release = $this->find($id);

        if (null === $release) {
            throw new ReleaseNotFoundException($id);
        }

        return $release;
    }

    public function store(ReleaseInterface $release): void
    {
        $this->flush($release);
    }
}
