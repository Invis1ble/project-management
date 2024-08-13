<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Diff;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Path;

final readonly class Diff
{
    public function __construct(
        public Path $oldPath,
        public Path $newPath,
        public Content $content,
        public bool $newFile,
        public bool $renamedFile,
        public bool $deletedFile,
    ) {
    }

    public function equals(self $other): bool
    {
        return $this->oldPath->equals($other->oldPath)
            && $this->newPath->equals($other->newPath)
            && $this->content->equals($other->content)
            && $this->newFile === $other->newFile
            && $this->renamedFile === $other->renamedFile
            && $this->deletedFile === $other->deletedFile
        ;
    }
}
