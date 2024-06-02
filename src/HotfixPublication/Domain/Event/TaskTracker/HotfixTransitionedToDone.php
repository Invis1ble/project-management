<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\TaskTracker;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;

final readonly class HotfixTransitionedToDone implements EventInterface
{
    public function __construct(
        public Project\Key $projectKey,
        public Issue\Key $key,
    ) {
    }
}
