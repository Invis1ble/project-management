<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Event\TaskTracker;

use ReleaseManagement\Shared\Domain\Event\TaskTracker\AbstractVersionEvent;

final readonly class ReleaseCandidateCreated extends AbstractVersionEvent
{
}
