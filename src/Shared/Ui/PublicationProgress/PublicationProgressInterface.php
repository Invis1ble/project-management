<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Ui\PublicationProgress;

use Invis1ble\Messenger\Event\EventInterface;

interface PublicationProgressInterface
{
    public function start(): void;

    public function setProgress(Step $step): void;

    public function finish(): void;

    public function setStatus(string $status, bool $display = true): void;

    public function addEvent(EventInterface $event): void;
}
