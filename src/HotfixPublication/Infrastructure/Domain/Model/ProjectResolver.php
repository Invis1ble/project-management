<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Infrastructure\Domain\Model;

use ProjectManagement\HotfixPublication\Domain\Model\ProjectResolverInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;

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
