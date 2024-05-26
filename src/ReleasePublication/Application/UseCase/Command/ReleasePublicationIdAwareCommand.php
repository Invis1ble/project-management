<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Application\UseCase\Command;

use Invis1ble\Messenger\Command\CommandInterface;
use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;

abstract readonly class ReleasePublicationIdAwareCommand implements CommandInterface
{
    public function __construct(public ReleasePublicationId $id)
    {
    }
}
