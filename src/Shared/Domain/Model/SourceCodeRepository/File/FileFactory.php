<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitId;

final readonly class FileFactory implements FileFactoryInterface
{
    public function createFile(
        string $fileName,
        string $filePath,
        string $content,
        string $ref,
        string $commitId,
        string $lastCommitId,
        bool $executeFilemode,
    ): File {
        return new File(
            fileName: Filename::fromString($fileName),
            filePath: FilePath::fromString($filePath),
            content: Content::fromBase64Encoded($content),
            ref: Branch\Name::fromString($ref),
            commitId: CommitId::fromString($commitId),
            lastCommitId: CommitId::fromString($lastCommitId),
            executeFilemode: $executeFilemode,
        );
    }
}
