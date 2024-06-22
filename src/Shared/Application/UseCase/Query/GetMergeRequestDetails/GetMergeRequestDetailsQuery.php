<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Application\UseCase\Query\GetMergeRequestDetails;

use Invis1ble\Messenger\Query\QueryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestIid;

final readonly class GetMergeRequestDetailsQuery implements QueryInterface
{
    public function __construct(
        public ProjectId $projectId,
        public MergeRequestIid $mergeRequestIid,
    ) {
    }
}
