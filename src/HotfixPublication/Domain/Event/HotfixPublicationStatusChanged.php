<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Event;

use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use ProjectManagement\HotfixPublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class HotfixPublicationStatusChanged extends AbstractHotfixPublicationEvent
{
    public function __construct(
        HotfixPublicationId $id,
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
