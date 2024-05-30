<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Application\Event;

use ProjectManagement\HotfixPublication\Application\UseCase\Command\ProceedToNextStatus\ProceedToNextStatusCommand;
use ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationStatusChanged;
use ProjectManagement\Shared\Application\Event\CommandBusAwareEventHandler;

final class HotfixPublicationStatusChangedHandler extends CommandBusAwareEventHandler
{
    public function __invoke(HotfixPublicationStatusChanged $event): void
    {
        $this->dispatch(new ProceedToNextStatusCommand($event->id));
    }
}
