<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details;

use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusInterface;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;

final readonly class Details
{
    public function __construct(
        public StatusInterface $status,
    ) {
    }

    public function merge(
        MergeRequestManagerInterface $mergeRequestManager,
        MergeRequest $context,
    ): MergeRequest {
        return $this->status->merge($mergeRequestManager, $context);
    }

    public function mergeable(): bool
    {
        return $this->status->mergeable();
    }
}
