<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Event;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;

abstract readonly class ReleasePublicationIdAwareEvent implements EventInterface
{
    public function __construct(public ReleasePublicationId $id)
    {
    }
}
