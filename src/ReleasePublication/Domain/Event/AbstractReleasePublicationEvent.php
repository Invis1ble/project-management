<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Event;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

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
