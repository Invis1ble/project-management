<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Infrastructure\Domain\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;
use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Exception\HotfixPublicationNotFoundException;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublication;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Repository\EventDispatchingRepository;

final class HotfixPublicationRepository extends EventDispatchingRepository implements HotfixPublicationRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        EventBusInterface $eventBus,
    ) {
        parent::__construct($registry, HotfixPublication::class, $eventBus);
    }

    public function contains(HotfixPublicationId $id): bool
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

    public function get(HotfixPublicationId $id): HotfixPublicationInterface
    {
        $publication = $this->find($id);
        $this->getEntityManager()->refresh($publication);

        if (null === $publication) {
            throw new HotfixPublicationNotFoundException("Hotfix publication $id not found.");
        }

        return $publication;
    }

    public function getLatestByTagName(Tag\VersionName $tagName): HotfixPublicationInterface
    {
        $hotfixPublication = $this->findOneBy(
            criteria: [
                'tagName' => $tagName,
            ],
            orderBy: [
                'createdAt' => 'DESC',
            ],
        );

        if (null === $hotfixPublication) {
            throw new HotfixPublicationNotFoundException("Hotfix publication with tag $tagName not found.");
        }

        return $hotfixPublication;
    }

    public function store(HotfixPublicationInterface $hotfixPublication): void
    {
        $this->persist($hotfixPublication);
    }
}
