<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\Event;

use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\ProceedToNextStatus\ProceedToNextStatusCommand;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationCreated;
use Invis1ble\ProjectManagement\Shared\Application\Event\CommandBusAwareEventHandler;

final class ReleasePublicationCreatedHandler extends CommandBusAwareEventHandler
{
    public function __invoke(ReleasePublicationCreated $event): void
    {
        $this->dispatch(new ProceedToNextStatusCommand($event->id));
    }
}
