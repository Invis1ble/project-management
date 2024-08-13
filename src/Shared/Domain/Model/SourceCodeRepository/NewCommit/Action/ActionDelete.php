<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Path;

final readonly class ActionDelete extends AbstractAction
{
    public function __construct(Path $filePath)
    {
        parent::__construct(Dictionary::Delete, $filePath);
    }
}
