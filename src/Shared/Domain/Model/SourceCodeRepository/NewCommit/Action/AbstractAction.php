<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;

abstract readonly class AbstractAction
{
    public function __construct(
        public Dictionary $action,
        public FilePath $filePath,
    ) {
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action->value,
            'file_path' => (string) $this->filePath,
        ];
    }
}
