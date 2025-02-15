<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Infrastructure\Domain\EventLog\EventFormatter;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationStatusChanged;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @extends AbstractFormatter<HotfixPublicationStatusChanged>
 */
final readonly class HotfixPublicationStatusChangedFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof HotfixPublicationStatusChanged;
    }

    public function format(EventInterface $event): string
    {
        return "Hotfixes publication $event->id `$event->tagName` status changed from `$event->previousStatus` to `$event->status`";
    }
}
