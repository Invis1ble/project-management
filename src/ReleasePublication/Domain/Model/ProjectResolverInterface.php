<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model;

use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;

interface ProjectResolverInterface
{
    public function frontend(ProjectId $projectId): bool;

    public function backend(ProjectId $projectId): bool;
}
