<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command;

use Invis1ble\Messenger\Command\CommandInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;

abstract readonly class ReleasePublicationIdAwareCommand implements CommandInterface
{
    public function __construct(public ReleasePublicationId $id)
    {
    }
}
