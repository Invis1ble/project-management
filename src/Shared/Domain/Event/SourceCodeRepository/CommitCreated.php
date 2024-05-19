<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event\SourceCodeRepository;

use Psr\Http\Message\UriInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitId;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Message;

final readonly class CommitCreated extends BranchNameAwareEvent
{
    public function __construct(
        ProjectId $projectId,
        Name $branchName,
        ?Name $startBranchName,
        public CommitId $commitId,
        public Message $message,
        public UriInterface $guiUrl,
    ) {
        parent::__construct($projectId, $branchName);
    }
}
