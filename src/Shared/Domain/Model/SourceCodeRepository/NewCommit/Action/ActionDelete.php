<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;

final readonly class ActionDelete extends AbstractAction
{
    public function __construct(FilePath $filePath)
    {
        parent::__construct(Dictionary::Delete, $filePath);
    }
}
