<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Title;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

abstract readonly class AbstractMergeRequestEvent implements EventInterface
{
    public function __construct(
        public ProjectId $projectId,
        public MergeRequestId $mergeRequestId,
        public Title $title,
        public Branch\Name $sourceBranchName,
        public Branch\Name $targetBranchName,
        public Details $details,
    ) {
    }
}
