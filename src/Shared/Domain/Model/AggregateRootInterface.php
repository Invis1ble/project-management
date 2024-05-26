<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model;

use Invis1ble\Messenger\Event\EventInterface;

interface AggregateRootInterface
{
    /**
     * @return EventInterface[]
     */
    public function popDomainEvents(): array;
}
