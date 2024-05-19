<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event\DevelopmentCollaboration;

use Invis1ble\Messenger\Event\EventInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;

final readonly class MergeRequestMerged implements EventInterface
{
    public function __construct(
        public ProjectId $projectId,
        public MergeRequestId $mergeRequestId,
        public Details $details,
    ) {
    }
}
