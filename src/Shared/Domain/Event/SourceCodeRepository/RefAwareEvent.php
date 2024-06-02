<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

abstract readonly class RefAwareEvent extends ProjectIdAwareEvent
{
    public function __construct(
        ProjectId $projectId,
        public Ref $ref,
    ) {
        parent::__construct($projectId);
    }
}
