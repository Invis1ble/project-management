<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\ContinuousIntegration\Job\Status;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status\Dictionary;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status\StatusFactory as StaticStatusFactory;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status\StatusFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status\StatusInterface;

final readonly class StatusFactory implements StatusFactoryInterface
{
    public function createStatus(Dictionary $name): StatusInterface
    {
        return StaticStatusFactory::createStatus($name);
    }
}
