<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\DevelopmentCollaboration\MergeRequest;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestStatusChanged;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @extends AbstractFormatter<MergeRequestStatusChanged>
 */
final readonly class MergeRequestStatusChangedFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof MergeRequestStatusChanged;
    }

    public function format(EventInterface $event): string
    {
        return "MR $event->guiUrl status changed from `{$event->previousStatus->value}` to `{$event->status->value}` (`$event->sourceBranchName` -> `$event->targetBranchName` | `$event->title`)";
    }
}
