<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Event;

use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

abstract readonly class AbstractReleasePublicationEvent extends ReleasePublicationIdAwareEvent
{
    public function __construct(
        ReleasePublicationId $id,
        public Name $branchName,
        public StatusInterface $status,
        public IssueList $readyToMergeTasks,
        public \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id);
    }
}
