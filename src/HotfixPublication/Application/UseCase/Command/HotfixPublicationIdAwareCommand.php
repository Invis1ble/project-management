<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command;

use Invis1ble\Messenger\Command\CommandInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;

abstract readonly class HotfixPublicationIdAwareCommand implements CommandInterface
{
    public function __construct(public HotfixPublicationId $id)
    {
    }
}
