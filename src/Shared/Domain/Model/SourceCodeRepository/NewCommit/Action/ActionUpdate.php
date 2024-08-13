<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Content;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Path;

final readonly class ActionUpdate extends AbstractAction
{
    public function __construct(
        Path $filePath,
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
