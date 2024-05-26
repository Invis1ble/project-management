<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication;

use Invis1ble\Messenger\Command\CommandInterface;
use ProjectManagement\HotfixPublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class CreateHotfixPublicationCommand implements CommandInterface
{
    public function __construct(
        public Name $branchName,
        public IssueList $readyToMergeTasks,
    ) {
    }
}
