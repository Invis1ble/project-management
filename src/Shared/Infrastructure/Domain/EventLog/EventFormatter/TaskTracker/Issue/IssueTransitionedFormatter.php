<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\TaskTracker\Issue;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\TaskTracker\Issue\IssueTransitioned;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @extends AbstractFormatter<IssueTransitioned>
 */
final readonly class IssueTransitionedFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof IssueTransitioned;
    }

    public function format(EventInterface $event): string
    {
        return "Issue `$event->key` transitioned to `$event->transitionName`";
    }
}
