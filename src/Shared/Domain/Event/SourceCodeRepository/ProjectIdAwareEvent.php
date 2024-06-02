<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;

abstract readonly class ProjectIdAwareEvent implements EventInterface
{
    public function __construct(
        public ProjectId $projectId,
    ) {
    }
}
