<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker\Transition;

use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition\Transition;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition\TransitionFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition\TransitionId;

final readonly class TransitionFactory implements TransitionFactoryInterface
{
    public function createTransition(
        string $id,
        string $name,
    ): Transition {
        return new Transition(
            id: TransitionId::fromString($id),
            name: Name::fromString($name),
        );
    }
}
