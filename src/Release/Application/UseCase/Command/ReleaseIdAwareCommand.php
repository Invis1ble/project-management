<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Application\UseCase\Command;

use Invis1ble\Messenger\Command\CommandInterface;
use ReleaseManagement\Release\Domain\Model\ReleaseId;

abstract readonly class ReleaseIdAwareCommand implements CommandInterface
{
    public function __construct(public ReleaseId $id)
    {
    }
}
