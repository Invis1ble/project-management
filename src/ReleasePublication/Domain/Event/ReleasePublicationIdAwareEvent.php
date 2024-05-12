<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Event;

use Invis1ble\Messenger\Event\EventInterface;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;

abstract readonly class ReleasePublicationIdAwareEvent implements EventInterface
{
    public function __construct(public ReleasePublicationId $id)
    {
    }
}
