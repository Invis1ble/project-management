<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Infrastructure\Domain\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Invis1ble\Messenger\Event\EventBusInterface;
use ReleaseManagement\ReleasePublication\Domain\Exception\ReleasePublicationNotFoundException;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublication;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ReleaseManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;
use ReleaseManagement\Shared\Domain\Repository\EventDispatchingRepository;

final class ReleasePublicationRepository extends EventDispatchingRepository implements ReleasePublicationRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        EventBusInterface $eventBus,
    ) {
        parent::__construct($registry, ReleasePublication::class, $eventBus);
    }

    public function get(ReleasePublicationId $id): ReleasePublicationInterface
    {
        $releasePublication = $this->find($id);

        if (null === $releasePublication) {
            throw new ReleasePublicationNotFoundException($id);
        }

        return $releasePublication;
    }

    public function store(ReleasePublicationInterface $releasePublication): void
    {
        $this->flush($releasePublication);
    }
}
