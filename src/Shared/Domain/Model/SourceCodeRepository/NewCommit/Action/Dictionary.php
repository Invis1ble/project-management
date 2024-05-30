<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

enum Dictionary: string
{
    case Create = 'create';

    case Delete = 'delete';

    case Move = 'move';

    case Update = 'update';

    case Chmod = 'chmod';
}
