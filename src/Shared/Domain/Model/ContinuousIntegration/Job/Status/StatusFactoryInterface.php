<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status;

interface StatusFactoryInterface
{
    public function createStatus(Dictionary $name): StatusInterface;
}
