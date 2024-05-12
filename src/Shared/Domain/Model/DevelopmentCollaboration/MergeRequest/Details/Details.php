<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details;

use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusInterface;

final readonly class Details
{
    public function __construct(
        public StatusInterface $status,
    ) {
    }

    public function mergeable(): bool
    {
        return $this->status->mergeable();
    }
}
