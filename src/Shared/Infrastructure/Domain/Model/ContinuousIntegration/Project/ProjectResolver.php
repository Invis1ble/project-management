<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\ContinuousIntegration\Project;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;

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
