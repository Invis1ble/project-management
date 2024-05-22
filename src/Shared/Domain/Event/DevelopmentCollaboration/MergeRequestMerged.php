<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration;

use Invis1ble\Messenger\Event\EventInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;

final readonly class MergeRequestMerged implements EventInterface
{
    public function __construct(
        public ProjectId $projectId,
        public MergeRequestId $mergeRequestId,
        public Details $details,
    ) {
    }
}
