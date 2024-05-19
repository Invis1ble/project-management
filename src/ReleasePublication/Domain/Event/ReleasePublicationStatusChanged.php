<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Event;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class ReleasePublicationStatusChanged extends AbstractReleasePublicationEvent
{
    public function __construct(
        ReleasePublicationId $id,
        Name $branchName,
        StatusInterface $status,
        public StatusInterface $previousStatus,
        IssueList $readyToMergeTasks,
        \DateTimeImmutable $createdAt,
    ) {
        parent::__construct(
            $id,
            $branchName,
            $status,
            $readyToMergeTasks,
            $createdAt,
        );
    }
}
