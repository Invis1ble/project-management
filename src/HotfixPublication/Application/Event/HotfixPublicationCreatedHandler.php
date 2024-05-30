<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Application\Event;

use ProjectManagement\HotfixPublication\Application\UseCase\Command\ProceedToNextStatus\ProceedToNextStatusCommand;
use ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationCreated;
use ProjectManagement\Shared\Application\Event\CommandBusAwareEventHandler;

final class HotfixPublicationCreatedHandler extends CommandBusAwareEventHandler
{
    public function __invoke(HotfixPublicationCreated $event): void
    {
        $this->dispatch(new ProceedToNextStatusCommand($event->id));
    }
}
