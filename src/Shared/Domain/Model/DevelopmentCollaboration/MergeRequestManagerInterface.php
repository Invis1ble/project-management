<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration;

use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;

interface MergeRequestManagerInterface
{
    public function merge(ProjectId $projectId, MergeRequestId $mergeRequestId): Details;

    public function supports(ProjectId $projectId): bool;

    public function details(ProjectId $projectId, MergeRequestId $mergeRequestId): Details;
}
