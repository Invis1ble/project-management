<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\TaskTracker\Issue;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\TaskTracker\Issue\IssueTransitioned;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\TaskTracker\Issue\IssueTransitionedFormatter;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\EventFormatterTestCase;

/**
 * @extends EventFormatterTestCase<IssueTransitioned>
 */
class IssueTransitionedFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): IssueTransitionedFormatter
    {
        return new IssueTransitionedFormatter();
    }

    protected function createEvent(): IssueTransitioned
    {
        return new IssueTransitioned(
            projectKey: Project\Key::fromString('TEST'),
            key: Issue\Key::fromString('TEST-1'),
            transitionId: Transition\TransitionId::fromString('123'),
            transitionName: Transition\Name::fromString('Close Issue'),
        );
    }

    protected function createExpectedMessage(EventInterface $event): string
    {
        return "Issue `$event->key` transitioned to `$event->transitionName`";
    }
}
