<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\SourceCodeRepository;

use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Message;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\File\File;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionList;

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
