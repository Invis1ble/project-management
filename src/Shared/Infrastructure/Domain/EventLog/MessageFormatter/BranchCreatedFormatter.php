<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\MessageFormatter;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Branch\BranchCreated;

final readonly class BranchCreatedFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof BranchCreated;
    }

    /**
     * @param BranchCreated $event
     */
    public function format(EventInterface $event): string
    {
        return "Branch `$event->name` created ($event->guiUrl)";
    }
}
