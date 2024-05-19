<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Exception;

use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;

class UnsupportedProjectException extends \InvalidArgumentException
{
    public function __construct(ProjectId $projectId)
    {
        parent::__construct("Unsupported project: $projectId");
    }
}
