<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\DevelopmentCollaboration\MergeRequest;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestCreated;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @extends AbstractFormatter<MergeRequestCreated>
 */
final readonly class MergeRequestCreatedFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof MergeRequestCreated;
    }

    public function format(EventInterface $event): string
    {
        return "MR $event->guiUrl created (`$event->sourceBranchName` -> `$event->targetBranchName` | `$event->title`)";
    }
}
