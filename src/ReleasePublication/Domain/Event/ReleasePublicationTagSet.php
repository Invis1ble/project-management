<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Event;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class ReleasePublicationTagSet extends AbstractReleasePublicationEvent
{
    public function __construct(
        ReleasePublicationId $id,
        Branch\Name $branchName,
        Tag\Name $tagName,
        Tag\Message $tagMessage,
        StatusInterface $status,
        IssueList $tasks,
        \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id, $branchName, $tagName, $tagMessage, $status, $tasks, $createdAt);
    }
}
