<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Content;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Path;

final readonly class ActionMove extends Action
{
    public function __construct(
        Path $filePath,
        public ?Content $content,
    ) {
        parent::__construct(Dictionary::Move, $filePath);
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        if (null !== $this->content) {
            $data['content'] = (string) $this->content;
        }

        return $data;
    }
}
