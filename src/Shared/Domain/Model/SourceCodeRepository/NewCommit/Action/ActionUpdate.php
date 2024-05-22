<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Content;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;

final readonly class ActionUpdate extends AbstractAction
{
    public function __construct(
        FilePath $filePath,
        public Content $content,
    ) {
        parent::__construct(Dictionary::Update, $filePath);
    }

    public function toArray(): array
    {
        return [
            'content' => (string) $this->content,
        ] + parent::toArray();
    }
}
