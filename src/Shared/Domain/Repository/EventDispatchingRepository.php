<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Invis1ble\Messenger\Event\EventBusInterface;
use ReleaseManagement\Shared\Domain\Model\AggregateRootInterface;

abstract class EventDispatchingRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        private readonly EventBusInterface $eventBus,
    ) {
        parent::__construct($registry, $entityClass);
    }

    public function flush(object $entity): void
    {
        $this->getEntityManager()->flush();

        if (!$entity instanceof AggregateRootInterface) {
            return;
        }

        foreach ($entity->popDomainEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
