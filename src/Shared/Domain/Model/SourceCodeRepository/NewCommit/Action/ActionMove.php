<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Content;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;

final readonly class ActionMove extends AbstractAction
{
    public function __construct(
        FilePath $filePath,
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
