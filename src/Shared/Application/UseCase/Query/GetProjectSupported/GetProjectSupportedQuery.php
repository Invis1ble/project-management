<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Application\UseCase\Query\GetProjectSupported;

use Invis1ble\Messenger\Query\QueryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;

final readonly class GetProjectSupportedQuery implements QueryInterface
{
    public function __construct(
        public ProjectId $projectId,
    ) {
    }
}
