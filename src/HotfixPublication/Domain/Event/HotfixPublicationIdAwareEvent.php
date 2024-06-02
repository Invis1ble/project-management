<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Event;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;

abstract readonly class HotfixPublicationIdAwareEvent implements EventInterface
{
    public function __construct(public HotfixPublicationId $id)
    {
    }
}
