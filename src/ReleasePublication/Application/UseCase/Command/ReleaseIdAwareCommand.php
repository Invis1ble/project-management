<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Application\UseCase\Command;

use Invis1ble\Messenger\Command\CommandInterface;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;

abstract readonly class ReleaseIdAwareCommand implements CommandInterface
{
    public function __construct(public ReleasePublicationId $id)
    {
    }
}
