<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Path;

final readonly class ActionChmod extends AbstractAction
{
    public function __construct(
        Path $filePath,
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
