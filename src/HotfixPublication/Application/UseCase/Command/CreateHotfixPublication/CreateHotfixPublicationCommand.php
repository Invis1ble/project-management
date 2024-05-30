<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication;

use Invis1ble\Messenger\Command\CommandInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class CreateHotfixPublicationCommand implements CommandInterface
{
    public function __construct(
        public Tag\VersionName $tagName,
        public Tag\Message $tagMessage,
        public IssueList $hotfixes,
    ) {
    }
}
