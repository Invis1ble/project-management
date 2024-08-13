<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitId;

final readonly class File
{
    public function __construct(
        public Filename $fileName,
        public Path $filePath,
        public Content $content,
        public Name $ref,
        public CommitId $commitId,
        public CommitId $lastCommitId,
        public bool $executeFilemode,
    ) {
    }
}
