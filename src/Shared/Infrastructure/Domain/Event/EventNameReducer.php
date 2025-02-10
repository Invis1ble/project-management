<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Event;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\EventNameReducerInterface;

class EventNameReducer implements EventNameReducerInterface
{
    public function reduce(EventInterface|string $event): string
    {
        if ($event instanceof EventInterface) {
            $event = $event::class;
        }

        [$vendor, $package] = $this->redundantSegments();

        return preg_replace(
            "/^$vendor\\\\$package\\\\/",
            '',
            $event,
        );
    }

    public function expand(string $name): string
    {
        [$vendor, $package] = $this->redundantSegments();

        return "$vendor\\$package\\$name";
    }

    private function redundantSegments(): array
    {
        $redundant = explode('\\', __NAMESPACE__, 3);

        return [$redundant[0], $redundant[1]];
    }
}
