<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\SourceCodeRepository\Branch;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Branch\BranchCreated;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @extends AbstractFormatter<BranchCreated>
 */
final readonly class BranchCreatedFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof BranchCreated;
    }

    public function format(EventInterface $event): string
    {
        return "Branch `$event->name` created ($event->guiUrl)";
    }
}
