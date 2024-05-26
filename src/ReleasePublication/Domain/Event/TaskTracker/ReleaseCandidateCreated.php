<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Event\TaskTracker;

use ProjectManagement\Shared\Domain\Event\TaskTracker\AbstractVersionEvent;

final readonly class ReleaseCandidateCreated extends AbstractVersionEvent
{
}
