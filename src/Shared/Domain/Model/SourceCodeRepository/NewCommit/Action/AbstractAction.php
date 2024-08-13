<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Path;

abstract readonly class AbstractAction
{
    public function __construct(
        public Dictionary $action,
        public Path $filePath,
    ) {
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action->value,
            'file_path' => (string) $this->filePath,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->action->equals($other->action)
            && $this->filePath->equals($other->filePath)
        ;
    }
}
