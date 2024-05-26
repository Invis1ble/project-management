<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Application\Event;

use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Command\CommandInterface;
use Invis1ble\Messenger\Event\EventHandlerInterface;

abstract class CommandBusAwareEventHandler implements EventHandlerInterface
{
    public function __construct(private readonly CommandBusInterface $commandBus)
    {
    }

    protected function dispatch(CommandInterface $command): void
    {
        $this->commandBus->dispatch($command);
    }
}
