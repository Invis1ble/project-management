<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Exception;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\Shared\Domain\Exception\PublicationNotFoundException;

class ReleasePublicationNotFoundException extends PublicationNotFoundException
{
    public function __construct(ReleasePublicationId $releasePublicationId)
    {
        parent::__construct("Release publication $releasePublicationId not found.");
    }
}
