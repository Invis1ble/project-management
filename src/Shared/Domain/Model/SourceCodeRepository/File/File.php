<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\File;

use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitId;

final readonly class File
{
    public function __construct(
        public Filename $filename,
        public FilePath $filePath,
        public Content $content,
        public Name $ref,
        public CommitId $commitId,
        public CommitId $lastCommitId,
        public bool $executeFilemode,
    ) {
    }
}