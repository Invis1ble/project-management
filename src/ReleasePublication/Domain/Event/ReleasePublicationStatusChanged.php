<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Event;

use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

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
