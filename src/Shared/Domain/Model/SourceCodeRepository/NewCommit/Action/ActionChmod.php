<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;

final readonly class ActionChmod extends AbstractAction
{
    public function __construct(
        FilePath $filePath,
        public bool $executeFilemode,
    ) {
        parent::__construct(Dictionary::Chmod, $filePath);
    }

    public function toArray(): array
    {
        return [
            'execute_filemode' => $this->executeFilemode,
        ] + parent::toArray();
    }
}
