<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Event;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

abstract readonly class AbstractHotfixPublicationEvent extends HotfixPublicationIdAwareEvent
{
    public function __construct(
        HotfixPublicationId $id,
        public Tag\VersionName $tagName,
        public Tag\Message $tagMessage,
        public StatusInterface $status,
        public IssueList $hotfixes,
        public \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id);
    }
}
