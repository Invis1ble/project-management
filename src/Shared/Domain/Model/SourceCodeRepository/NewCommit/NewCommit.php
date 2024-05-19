<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit;

use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Message;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionList;

final readonly class NewCommit
{
    public function __construct(
        public ProjectId $projectId,
        public Name $branchName,
        public Message $message,
        public ActionList $actions,
        public ?Name $startBranchName = null,
    ) {
    }
}
