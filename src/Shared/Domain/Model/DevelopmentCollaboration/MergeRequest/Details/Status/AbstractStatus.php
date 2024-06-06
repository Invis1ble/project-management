<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;

abstract readonly class AbstractStatus implements StatusInterface
{
    public function merge(
        MergeRequestManagerInterface $mergeRequestManager,
        MergeRequest $context,
    ): MergeRequest {
        return $mergeRequestManager->mergeMergeRequest(
            projectId: $context->projectId,
            mergeRequestId: $context->id,
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

    public function equals(StatusInterface $other): bool
    {
        return $other::class === $this::class;
    }
}
