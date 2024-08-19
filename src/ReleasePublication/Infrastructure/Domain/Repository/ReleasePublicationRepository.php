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
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
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

        if (null === $publication) {
            throw new ReleasePublicationNotFoundException("Release publication $id not found.");
        }

        $this->getEntityManager()->refresh($publication);

        return $publication;
    }

    public function getLatest(): ReleasePublicationInterface
    {
        $publication = $this->findLatestByCriteria();

        if (null === $publication) {
            throw new ReleasePublicationNotFoundException('No release publications.');
        }

        return $publication;
    }

    public function getLatestByTagName(Tag\VersionName $tagName): ReleasePublicationInterface
    {
        $publication = $this->findLatestByCriteria([
            'tagName' => $tagName,
        ]);

        if (null === $publication) {
            throw new ReleasePublicationNotFoundException("Release publication with tag $tagName not found.");
        }

        return $publication;
    }

    public function store(ReleasePublicationInterface $releasePublication): void
    {
        $this->persist($releasePublication);
    }

    private function findLatestByCriteria(array $criteria = []): ?ReleasePublicationInterface
    {
        return $this->findOneBy(
            criteria: $criteria,
            orderBy: [
                'createdAt' => 'DESC',
            ],
        );
    }
}
