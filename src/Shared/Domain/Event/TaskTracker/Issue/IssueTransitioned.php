<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\TaskTracker\Issue;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition;

final readonly class IssueTransitioned implements EventInterface
{
    public function __construct(
        public Project\Key $projectKey,
        public Issue\Key $key,
        public Transition\TransitionId $transitionId,
        public Transition\Name $transitionName,
    ) {
    }
}
