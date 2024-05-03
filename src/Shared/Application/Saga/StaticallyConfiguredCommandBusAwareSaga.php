<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Application\Saga;

use Broadway\Saga\Metadata\StaticallyConfiguredSagaInterface;
use Broadway\Saga\Saga;
use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Command\CommandInterface;

abstract class StaticallyConfiguredCommandBusAwareSaga extends Saga implements StaticallyConfiguredSagaInterface
{
    public function __construct(private readonly CommandBusInterface $commandBus)
    {
    }

    protected function dispatchCommand(CommandInterface $command): void
    {
        $this->commandBus->dispatch($command);
    }
}
