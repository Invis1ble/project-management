<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Status;

abstract readonly class AbstractStatus implements StatusInterface
{
    public function merge(
        MergeRequestManagerInterface $mergeRequestManager,
        MergeRequest $context,
    ): MergeRequest {
        return $mergeRequestManager->mergeMergeRequest(
            projectId: $context->projectId,
            mergeRequestIid: $context->iid,
        );
    }

    public function mayBeMergeable(): bool
    {
        return false;
    }

    public function mergeable(): bool
    {
        return false;
    }

    public function toTaskTrackerStatus(): Status
    {
        if ($this->mayBeMergeable()) {
            return Status::Open;
        }

        return Status::Declined;
    }

    public function equals(StatusInterface $other): bool
    {
        return $other::class === static::class;
    }
}
