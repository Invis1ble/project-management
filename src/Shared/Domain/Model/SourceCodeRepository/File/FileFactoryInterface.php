<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File;

interface FileFactoryInterface
{
    public function createFile(
        string $fileName,
        string $filePath,
        string $content,
        string $ref,
        string $commitId,
        string $lastCommitId,
        bool $executeFilemode,
    ): File;
}
