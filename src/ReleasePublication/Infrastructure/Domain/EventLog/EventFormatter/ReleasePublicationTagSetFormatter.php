<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\EventLog\EventFormatter;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationTagSet;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @extends AbstractFormatter<ReleasePublicationTagSet>
 */
final readonly class ReleasePublicationTagSetFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof ReleasePublicationTagSet;
    }

    public function format(EventInterface $event): string
    {
        return "Release publication $event->id `$event->tagName` tag `$event->tagName` set";
    }
}
