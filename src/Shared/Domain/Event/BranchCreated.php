<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event;

use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

final readonly class BranchCreated extends BranchNameAwareEvent
{
    public function __construct(
        Name $branchName,
        public ProjectId $projectId,
    ) {
        parent::__construct($branchName);
    }
}
