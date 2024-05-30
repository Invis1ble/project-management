<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\Event;

use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\ProceedToNextStatus\ProceedToNextStatusCommand;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationStatusChanged;
use Invis1ble\ProjectManagement\Shared\Application\Event\CommandBusAwareEventHandler;

final class ReleasePublicationStatusChangedHandler extends CommandBusAwareEventHandler
{
    public function __invoke(ReleasePublicationStatusChanged $event): void
    {
        if ($event->status->prepared()) {
            return;
        }

        $this->dispatch(new ProceedToNextStatusCommand($event->id));
    }
}
