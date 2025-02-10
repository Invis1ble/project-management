<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event;

use Invis1ble\Messenger\Event\EventInterface;

interface EventNameReducerInterface
{
    public function reduce(EventInterface|string $event): string;

    public function expand(string $name): string;
}
