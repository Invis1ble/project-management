<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Message;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class NewCommit
{
    public function __construct(
        public ProjectId $projectId,
        public Name $branchName,
        public Message $message,
        public ActionList $actions,
        public ?Name $startBranchName = null,
    ) {
    }

    public function commit(SourceCodeRepositoryInterface $repository): Commit
    {
        return $repository->commit(
            branchName: $this->branchName,
            message: $this->message,
            actions: $this->actions,
            startBranchName: $this->startBranchName,
        );
    }
}
