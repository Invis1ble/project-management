<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Exception;

use ReleaseManagement\Release\Domain\Model\ReleaseId;
use ReleaseManagement\Shared\Domain\Exception\NotFoundException;

class ReleaseNotFoundException extends NotFoundException
{
    public function __construct(ReleaseId $releaseId)
    {
        parent::__construct("Release $releaseId not found.");
    }
}
