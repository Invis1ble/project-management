<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Exception;

use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;

class UnsupportedProjectException extends \InvalidArgumentException
{
    public function __construct(ProjectId $projectId)
    {
        parent::__construct("Unsupported project: $projectId");
    }
}
