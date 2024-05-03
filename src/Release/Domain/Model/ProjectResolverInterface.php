<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model;

use ReleaseManagement\Shared\Domain\Model\ProjectId;

interface ProjectResolverInterface
{
    public function frontend(ProjectId $projectId): bool;

    public function backend(ProjectId $projectId): bool;
}
