<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Application\UseCase\Query\GetMergeRequestDetails;

use Invis1ble\Messenger\Query\QueryInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;

final readonly class GetMergeRequestDetailsQuery implements QueryInterface
{
    public function __construct(
        public ProjectId $projectId,
        public MergeRequestId $mergeRequestId,
    ) {
    }
}
