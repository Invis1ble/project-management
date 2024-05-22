<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model;

use Invis1ble\Messenger\Event\EventInterface;

trait AggregateRootTrait
{
    /**
     * @var EventInterface[]
     */
    protected array $domainEvents = [];

    /**
     * @return EventInterface[]
     */
    public function popDomainEvents(): array
    {
        $events = $this->domainEvents;

        $this->domainEvents = [];

        return $events;
    }

    protected function raiseDomainEvent(EventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }
}
