<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;

interface StatusProviderInterface
{
    public function readyToMerge(): Status;

    public function readyForPublish(): Status;

    public function releaseCandidate(): Status;

    public function done(): Status;
}
