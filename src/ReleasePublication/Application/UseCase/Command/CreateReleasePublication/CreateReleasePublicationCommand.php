<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\CreateReleasePublication;

use Invis1ble\Messenger\Command\CommandInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class CreateReleasePublicationCommand implements CommandInterface
{
    public function __construct(
        public Name $branchName,
        public IssueList $tasks,
    ) {
    }
}
