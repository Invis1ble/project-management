<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;

final readonly class ActionDelete extends AbstractAction
{
    public function __construct(FilePath $filePath)
    {
        parent::__construct(Dictionary::Delete, $filePath);
    }
}
