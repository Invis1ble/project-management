<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Event;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class HotfixPublicationStatusChanged extends AbstractHotfixPublicationEvent
{
    public function __construct(
        HotfixPublicationId $id,
        StatusInterface $status,
        public StatusInterface $previousStatus,
        IssueList $readyToMergeTasks,
        \DateTimeImmutable $createdAt,
    ) {
        parent::__construct(
            $id,
            $status,
            $readyToMergeTasks,
            $createdAt,
        );
    }
}
