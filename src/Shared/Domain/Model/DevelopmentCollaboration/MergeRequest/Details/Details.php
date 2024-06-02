<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;

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

    public function mayBeMergeable(): bool
    {
        return $this->status->mayBeMergeable();
    }

    public function mergeable(): bool
    {
        return $this->status->mergeable();
    }
}
