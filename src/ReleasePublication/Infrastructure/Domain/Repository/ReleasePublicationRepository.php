<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;
use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Exception\ReleasePublicationNotFoundException;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublication;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Repository\EventDispatchingRepository;

final class ReleasePublicationRepository extends EventDispatchingRepository implements ReleasePublicationRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        EventBusInterface $eventBus,
    ) {
        parent::__construct($registry, ReleasePublication::class, $eventBus);
    }

    public function contains(ReleasePublicationId $id): bool
    {
        $result = $this->createQueryBuilder('p')
            ->select('1')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR)
        ;

        return null !== $result;
    }

    public function get(ReleasePublicationId $id): ReleasePublicationInterface
    {
        $publication = $this->find($id);
        $this->getEntityManager()->refresh($publication);

        if (null === $publication) {
            throw new ReleasePublicationNotFoundException($id);
        }

        return $publication;
    }

    public function store(ReleasePublicationInterface $releasePublication): void
    {
        $this->persist($releasePublication);
    }
}
