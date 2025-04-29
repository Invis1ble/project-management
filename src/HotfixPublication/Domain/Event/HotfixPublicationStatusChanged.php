<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Event;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class HotfixPublicationStatusChanged extends AbstractHotfixPublicationStatusSetEvent
{
    public function __construct(
        HotfixPublicationId $id,
        Tag\VersionName $tagName,
        Tag\Message $tagMessage,
        StatusInterface $status,
        public StatusInterface $previousStatus,
        IssueList $hotfixes,
        \DateTimeImmutable $createdAt,
    ) {
        parent::__construct(
            id: $id,
            tagName: $tagName,
            tagMessage: $tagMessage,
            status: $status,
            hotfixes: $hotfixes,
            createdAt: $createdAt,
        );
    }
}
