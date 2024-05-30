<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication;

use Invis1ble\Messenger\Command\CommandInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class CreateHotfixPublicationCommand implements CommandInterface
{
    public function __construct(
        public Tag\VersionName $tagName,
        public Tag\Message $tagMessage,
        public IssueList $hotfixes,
    ) {
    }
}
