<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Commit;

use ProjectManagement\Shared\Domain\Event\SourceCodeRepository\ProjectIdAwareEvent;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Message;
use Psr\Http\Message\UriInterface;

final readonly class CommitCreated extends ProjectIdAwareEvent
{
    public function __construct(
        ProjectId $projectId,
        public Branch\Name $branchName,
        public ?Branch\Name $startBranchName,
        public CommitId $commitId,
        public Message $message,
        public UriInterface $guiUrl,
        public \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($projectId);
    }
}
