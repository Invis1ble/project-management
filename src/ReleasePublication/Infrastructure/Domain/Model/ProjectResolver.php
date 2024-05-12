<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Infrastructure\Domain\Model;

use ReleaseManagement\ReleasePublication\Domain\Model\ProjectResolverInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;

final readonly class ProjectResolver implements ProjectResolverInterface
{
    public function __construct(
        private ProjectId $frontendProjectId,
        private ProjectId $backendProjectId,
    ) {
    }

    public function frontend(ProjectId $projectId): bool
    {
        return $this->frontendProjectId->equals($projectId);
    }

    public function backend(ProjectId $projectId): bool
    {
        return $this->backendProjectId->equals($projectId);
    }
}
