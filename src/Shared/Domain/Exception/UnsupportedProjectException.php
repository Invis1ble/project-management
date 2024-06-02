<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Exception;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;

class UnsupportedProjectException extends \InvalidArgumentException
{
    public function __construct(ProjectId $projectId)
    {
        parent::__construct("Unsupported project: $projectId");
    }
}
