<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Exception;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\Shared\Domain\Exception\NotFoundException;

class HotfixPublicationNotFoundException extends NotFoundException
{
    public function __construct(HotfixPublicationId $publicationId)
    {
        parent::__construct("Hotfix publication $publicationId not found.");
    }
}
