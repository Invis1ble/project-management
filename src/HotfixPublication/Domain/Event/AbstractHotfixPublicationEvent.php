<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Event;

use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

abstract readonly class AbstractHotfixPublicationEvent extends HotfixPublicationIdAwareEvent
{
    public function __construct(
        HotfixPublicationId $id,
        public StatusInterface $status,
        public IssueList $hotfixes,
        public \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id);
    }
}
