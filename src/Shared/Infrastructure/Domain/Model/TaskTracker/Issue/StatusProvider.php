<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker\Issue;

use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;

final readonly class StatusProvider implements StatusProviderInterface
{
    public function __construct(
        private Status $statusDone,
        private Status $statusReadyForPublish,
        private Status $statusReadyToMerge,
        private Status $statusReleaseCandidate,
    ) {
    }

    public function done(): Status
    {
        return $this->statusDone;
    }

    public function readyForPublish(): Status
    {
        return $this->statusReadyForPublish;
    }

    public function readyToMerge(): Status
    {
        return $this->statusReadyToMerge;
    }

    public function releaseCandidate(): Status
    {
        return $this->statusReleaseCandidate;
    }
}
