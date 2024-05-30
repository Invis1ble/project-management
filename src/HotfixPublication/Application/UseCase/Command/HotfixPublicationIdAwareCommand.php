<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Application\UseCase\Command;

use Invis1ble\Messenger\Command\CommandInterface;
use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;

abstract readonly class HotfixPublicationIdAwareCommand implements CommandInterface
{
    public function __construct(public HotfixPublicationId $id)
    {
    }
}
