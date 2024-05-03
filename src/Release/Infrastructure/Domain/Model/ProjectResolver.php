<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Infrastructure\Domain\Model;

use ReleaseManagement\Release\Domain\Model\ProjectResolverInterface;
use ReleaseManagement\Shared\Domain\Model\ProjectId;

final readonly class ProjectResolver implements ProjectResolverInterface
{
    private ProjectId $frontendProjectId;

    private ProjectId $backendProjectId;

    public function __construct(
        int $frontendProjectId,
        int $backendProjectId,
    ) {
        $this->frontendProjectId = ProjectId::from($frontendProjectId);
        $this->backendProjectId = ProjectId::from($backendProjectId);
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
