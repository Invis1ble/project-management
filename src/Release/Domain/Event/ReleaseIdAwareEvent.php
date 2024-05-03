<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Event;

use Invis1ble\Messenger\Event\EventInterface;
use ReleaseManagement\Release\Domain\Model\ReleaseId;

abstract readonly class ReleaseIdAwareEvent implements EventInterface
{
    public function __construct(public ReleaseId $id)
    {
    }
}
