<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Exception;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ReleaseManagement\Shared\Domain\Exception\NotFoundException;

class ReleasePublicationNotFoundException extends NotFoundException
{
    public function __construct(ReleasePublicationId $releasePublicationId)
    {
        parent::__construct("Release publication $releasePublicationId not found.");
    }
}
