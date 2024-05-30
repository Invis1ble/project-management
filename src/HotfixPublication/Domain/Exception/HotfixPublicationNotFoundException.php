<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Exception;

use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use ProjectManagement\Shared\Domain\Exception\NotFoundException;

class HotfixPublicationNotFoundException extends NotFoundException
{
    public function __construct(HotfixPublicationId $publicationId)
    {
        parent::__construct("Hotfix publication $publicationId not found.");
    }
}
