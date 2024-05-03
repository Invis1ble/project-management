<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event;

use ReleaseManagement\Shared\Domain\Model\BranchName;

final readonly class LatestPipelineStuck extends BranchNameAwareEvent
{
    public function __construct(
        BranchName $branchName,
        public \DateInterval $maxAwaitingTime,
    ) {
        parent::__construct($branchName);
    }
}
