<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Infrastructure\Domain\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Invis1ble\Messenger\Event\EventBusInterface;
use ProjectManagement\ReleasePublication\Domain\Exception\ReleasePublicationNotFoundException;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublication;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ProjectManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;
use ProjectManagement\Shared\Domain\Repository\EventDispatchingRepository;

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
        $this->persist($releasePublication);
    }
}
