<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event\SourceCodeRepository;

use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

final readonly class BranchCreated extends BranchNameAwareEvent
{
    public function __construct(
        ProjectId $projectId,
        Name $branchName,
        public Name $ref,
    ) {
        parent::__construct($projectId, $branchName);
    }
}
