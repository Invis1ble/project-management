<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Exception;

use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ProjectManagement\Shared\Domain\Exception\NotFoundException;

class ReleasePublicationNotFoundException extends NotFoundException
{
    public function __construct(ReleasePublicationId $releasePublicationId)
    {
        parent::__construct("Release publication $releasePublicationId not found.");
    }
}
