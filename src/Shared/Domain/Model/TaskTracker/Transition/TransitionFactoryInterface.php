<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition;

interface TransitionFactoryInterface
{
    public function createTransition(
        string $id,
        string $name,
    ): Transition;
}
