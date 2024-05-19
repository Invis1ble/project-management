<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\File\Content;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;

final readonly class ActionCreate extends AbstractAction
{
    public function __construct(
        FilePath $filePath,
        public Content $content,
    ) {
        parent::__construct(Dictionary::Create, $filePath);
    }

    public function toArray(): array
    {
        return [
            'content' => (string) $this->content,
        ] + parent::toArray();
    }
}