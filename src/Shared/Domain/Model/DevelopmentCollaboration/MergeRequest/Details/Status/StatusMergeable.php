<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;

final readonly class StatusMergeable extends AbstractStatus
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
        return true;
    }

    public function mergeable(): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return Dictionary::Mergeable->value;
    }
}
