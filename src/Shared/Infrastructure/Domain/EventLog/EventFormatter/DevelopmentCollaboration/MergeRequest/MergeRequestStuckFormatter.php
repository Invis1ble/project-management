<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\DevelopmentCollaboration\MergeRequest;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestStuck;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @extends AbstractFormatter<MergeRequestStuck>
 */
final readonly class MergeRequestStuckFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof MergeRequestStuck;
    }

    public function format(EventInterface $event): string
    {
        return "MR $event->guiUrl stuck in status `{$event->status->value}` (`$event->sourceBranchName` -> `$event->targetBranchName` | `$event->title`)";
    }
}
