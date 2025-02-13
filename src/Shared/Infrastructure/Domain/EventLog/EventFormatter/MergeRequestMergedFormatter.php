<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestMerged;

/**
 * @extends AbstractFormatter<MergeRequestMerged>
 */
final readonly class MergeRequestMergedFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof MergeRequestMerged;
    }

    public function format(EventInterface $event): string
    {
        return "MR $event->guiUrl merged ($event->sourceBranchName -> $event->targetBranchName | $event->title)";
    }
}
