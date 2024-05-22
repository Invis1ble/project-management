<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Event\SourceCodeRepository;

use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

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
