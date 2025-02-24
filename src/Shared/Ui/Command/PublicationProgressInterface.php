<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Ui\Command;

use Invis1ble\Messenger\Event\EventInterface;

interface PublicationProgressInterface
{
    public function start(string $status = 'inited'): void;

    public function advance(int $step = 1): void;

    public function finish(): void;

    public function setStatus(string $status, bool $display = true): void;

    public function addEvent(EventInterface $event): void;
}
