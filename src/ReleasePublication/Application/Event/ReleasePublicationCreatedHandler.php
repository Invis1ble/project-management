<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Application\Event;

use ProjectManagement\ReleasePublication\Application\UseCase\Command\ProceedToNextStatus\ProceedToNextStatusCommand;
use ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationCreated;
use ProjectManagement\Shared\Application\Event\CommandBusAwareEventHandler;

final class ReleasePublicationCreatedHandler extends CommandBusAwareEventHandler
{
    public function __invoke(ReleasePublicationCreated $event): void
    {
        if ($event->status->prepared()) {
            return;
        }

        $this->dispatch(new ProceedToNextStatusCommand($event->id));
    }
}
