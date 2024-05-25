<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Event\SourceCodeRepository;

use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Message;
use Psr\Http\Message\UriInterface;

final readonly class CommitCreated extends BranchNameAwareEvent
{
    public function __construct(
        ProjectId $projectId,
        Name $branchName,
        public ?Name $startBranchName,
        public CommitId $commitId,
        public Message $message,
        public UriInterface $guiUrl,
    ) {
        parent::__construct($projectId, $branchName);
    }
}
