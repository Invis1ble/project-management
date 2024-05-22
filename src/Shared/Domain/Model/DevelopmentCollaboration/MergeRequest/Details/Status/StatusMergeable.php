<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Status;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;

final readonly class StatusMergeable extends AbstractStatus
{
    public function merge(
        MergeRequestManagerInterface $mergeRequestManager,
        MergeRequest $context,
    ): MergeRequest {
        $details = $mergeRequestManager->merge(
            projectId: $context->projectId,
            mergeRequestId: $context->id,
        );

        return new MergeRequest(
            id: $context->id,
            name: $context->name,
            projectId: $context->projectId,
            projectName: $context->projectName,
            sourceBranchName: $context->sourceBranchName,
            targetBranchName: $context->targetBranchName,
            status: Status::Merged,
            guiUrl: $context->guiUrl,
            details: $details,
        );
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
