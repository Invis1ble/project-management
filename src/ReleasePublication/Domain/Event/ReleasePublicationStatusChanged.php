<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Event;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class ReleasePublicationStatusChanged extends AbstractReleasePublicationEvent
{
    public function __construct(
        ReleasePublicationId $id,
        Name $branchName,
        ?Tag\Name $tagName,
        ?Tag\Message $tagMessage,
        public StatusInterface $previousStatus,
        StatusInterface $status,
        IssueList $readyToMergeTasks,
        \DateTimeImmutable $createdAt,
    ) {
        parent::__construct(
            $id,
            $branchName,
            $tagName,
            $tagMessage,
            $status,
            $readyToMergeTasks,
            $createdAt,
        );
    }
}
