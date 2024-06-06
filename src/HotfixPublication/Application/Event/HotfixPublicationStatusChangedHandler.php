<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\Event;

use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\ProceedToNextStatus\ProceedToNextStatusCommand;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationStatusChanged;
use Invis1ble\ProjectManagement\Shared\Application\Event\CommandBusAwareEventHandler;

final class HotfixPublicationStatusChangedHandler extends CommandBusAwareEventHandler
{
    public function __invoke(HotfixPublicationStatusChanged $event): void
    {
        // $this->dispatch(new ProceedToNextStatusCommand($event->id));
    }
}
