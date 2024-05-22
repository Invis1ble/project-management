<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository;

use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Message;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\File;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionList;

interface SourceCodeRepositoryInterface
{
    public function createBranch(Name $name, Name $ref): void;

    public function commit(
        Name $branchName,
        Message $message,
        ActionList $actions,
        ?Name $startBranchName = null,
    ): void;

    public function file(Name $branchName, FilePath $filePath): File;
}
