<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Event;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

abstract readonly class AbstractReleasePublicationEvent extends ReleasePublicationIdAwareEvent
{
    public function __construct(
        ReleasePublicationId $id,
        public Branch\Name $branchName,
        public ?Tag\Name $tagName,
        public ?Tag\Message $tagMessage,
        public StatusInterface $status,
        public IssueList $tasks,
        public \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id);
    }
}
