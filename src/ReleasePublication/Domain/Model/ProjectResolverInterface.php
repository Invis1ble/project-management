<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model;

use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;

interface ProjectResolverInterface
{
    public function frontend(ProjectId $projectId): bool;

    public function backend(ProjectId $projectId): bool;
}
