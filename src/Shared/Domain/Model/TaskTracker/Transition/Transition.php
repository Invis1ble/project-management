<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition;

final readonly class Transition
{
    public function __construct(
        public TransitionId $id,
        public Name $name,
    ) {
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id)
            && $this->name->equals($other->name)
        ;
    }
}
