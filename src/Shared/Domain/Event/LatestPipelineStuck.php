<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event;

use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

final readonly class LatestPipelineStuck extends BranchNameAwareEvent
{
    public function __construct(
        Name $branchName,
        public \DateInterval $maxAwaitingTime,
    ) {
        parent::__construct($branchName);
    }
}
