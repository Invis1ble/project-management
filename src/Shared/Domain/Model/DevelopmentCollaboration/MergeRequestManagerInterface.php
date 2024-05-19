<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration;

use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;

interface MergeRequestManagerInterface
{
    public function merge(ProjectId $projectId, MergeRequestId $mergeRequestId): Details;

    public function supports(ProjectId $projectId): bool;

    public function details(ProjectId $projectId, MergeRequestId $mergeRequestId): Details;
}
