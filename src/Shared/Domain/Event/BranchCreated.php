<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event;

use ReleaseManagement\Shared\Domain\Model\BranchName;
use ReleaseManagement\Shared\Domain\Model\ProjectId;

final readonly class BranchCreated extends BranchNameAwareEvent
{
    public function __construct(
        BranchName $branchName,
        public ProjectId $projectId,
    ) {
        parent::__construct($branchName);
    }
}
