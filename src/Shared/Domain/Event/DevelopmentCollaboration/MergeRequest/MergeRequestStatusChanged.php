<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestIid;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Title;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Psr\Http\Message\UriInterface;

final readonly class MergeRequestStatusChanged extends AbstractMergeRequestEvent
{
    public function __construct(
        Project\ProjectId $projectId,
        Project\Name $projectName,
        MergeRequestIid $mergeRequestIid,
        Title $title,
        Branch\Name $sourceBranchName,
        Branch\Name $targetBranchName,
        public ?Status $previousStatus,
        Status $status,
        UriInterface $guiUrl,
        public ?Details $previousDetails,
        Details $details,
        public \DateInterval $tickInterval,
        public \DateInterval $maxAwaitingTime,
    ) {
        parent::__construct(
            $projectId,
            $projectName,
            $mergeRequestIid,
            $title,
            $sourceBranchName,
            $targetBranchName,
            $status,
            $guiUrl,
            $details,
        );
    }
}
