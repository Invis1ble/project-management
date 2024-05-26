<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Event;

use Invis1ble\Messenger\Event\EventInterface;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;

abstract readonly class HotfixPublicationIdAwareEvent implements EventInterface
{
    public function __construct(public HotfixPublicationId $id)
    {
    }
}
