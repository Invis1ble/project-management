<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Path;

abstract readonly class Action
{
    public function __construct(
        public Dictionary $name,
        public Path $filePath,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name->value,
            'file_path' => (string) $this->filePath,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->toArray() === $other->toArray();
    }
}
