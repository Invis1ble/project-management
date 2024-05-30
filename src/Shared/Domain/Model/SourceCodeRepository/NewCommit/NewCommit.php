<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit;

use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Commit;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Message;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionList;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

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
