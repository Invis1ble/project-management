<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Application\UseCase\Query\GetProjectSupported;

use Invis1ble\Messenger\Query\QueryInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;

final readonly class GetProjectSupportedQuery implements QueryInterface
{
    public function __construct(
        public ProjectId $projectId,
    ) {
    }
}
