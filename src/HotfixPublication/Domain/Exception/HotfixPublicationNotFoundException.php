<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Exception;

use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use ProjectManagement\Shared\Domain\Exception\NotFoundException;

class HotfixPublicationNotFoundException extends NotFoundException
{
    public function __construct(HotfixPublicationId $hotfixPublicationId)
    {
        parent::__construct("Release publication $hotfixPublicationId not found.");
    }
}
