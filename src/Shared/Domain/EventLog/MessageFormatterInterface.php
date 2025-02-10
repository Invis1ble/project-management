<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\EventLog;

use Invis1ble\Messenger\Event\EventInterface;

interface MessageFormatterInterface
{
    public function supports(EventInterface $event): bool;

    public function format(EventInterface $event): string;
}
