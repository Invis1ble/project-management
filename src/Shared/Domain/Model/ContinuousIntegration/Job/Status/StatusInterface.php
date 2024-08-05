<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status;

interface StatusInterface extends \Stringable
{
    public function finished(): bool;

    public function inProgress(): bool;

    public function equals(self $other): bool;
}
