<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;

interface ProjectResolverInterface
{
    public function frontend(ProjectId $projectId): bool;

    public function backend(ProjectId $projectId): bool;
}
