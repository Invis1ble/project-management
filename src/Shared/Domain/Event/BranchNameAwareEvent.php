<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event;

use Invis1ble\Messenger\Event\EventInterface;
use ReleaseManagement\Shared\Domain\Model\BranchName;

abstract readonly class BranchNameAwareEvent implements EventInterface
{
    public function __construct(public BranchName $branchName)
    {
    }
}
