<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\EventLog;

use Invis1ble\Messenger\Event\EventInterface;

interface EventFormatterStackInterface
{
    public function setFormat(string $format): void;

    public function setTimeFormat(string $timeFormat): void;

    public function format(EventInterface $event): string;
}
