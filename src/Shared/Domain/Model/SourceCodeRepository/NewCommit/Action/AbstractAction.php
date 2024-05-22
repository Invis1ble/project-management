<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;

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
