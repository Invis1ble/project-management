<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Status;

interface StatusInterface extends \Stringable
{
    public function mayBeMergeable(): bool;

    public function mergeable(): bool;

    public function merge(
        MergeRequestManagerInterface $mergeRequestManager,
        MergeRequest $context,
    ): MergeRequest;

    public function toTaskTrackerStatus(): Status;

    public function equals(self $other): bool;
}
