<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Invis1ble\Messenger\Event\EventBusInterface;
use ProjectManagement\Shared\Domain\Model\AggregateRootInterface;

abstract class EventDispatchingRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        private readonly EventBusInterface $eventBus,
    ) {
        parent::__construct($registry, $entityClass);
    }

    protected function persist(object $entity): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();

        if (!$entity instanceof AggregateRootInterface) {
            return;
        }

        foreach ($entity->popDomainEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
