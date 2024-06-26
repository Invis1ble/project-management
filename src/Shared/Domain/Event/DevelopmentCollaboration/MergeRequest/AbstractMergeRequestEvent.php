<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestIid;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Title;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Psr\Http\Message\UriInterface;

abstract readonly class AbstractMergeRequestEvent implements EventInterface
{
    public function __construct(
        public Project\ProjectId $projectId,
        public Project\Name $projectName,
        public MergeRequestIid $mergeRequestIid,
        public Title $title,
        public Branch\Name $sourceBranchName,
        public Branch\Name $targetBranchName,
        public Status $status,
        public UriInterface $guiUrl,
        public Details $details,
    ) {
    }
}
