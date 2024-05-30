<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Event\TaskTracker;

use Invis1ble\Messenger\Event\EventInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Project;

final readonly class HotfixTransitionedToDone implements EventInterface
{
    public function __construct(
        public Project\Key $projectKey,
        public Issue\Key $key,
    ) {
    }
}
