<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\EventLog;

use Invis1ble\Messenger\Event\EventInterface;

/**
 * @template T of EventInterface
 */
interface EventFormatterInterface
{
    /**
     * @param T $event
     */
    public function supports(EventInterface $event): bool;

    /**
     * @param T $event
     */
    public function format(EventInterface $event): string;
}
