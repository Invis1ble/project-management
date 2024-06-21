<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

abstract readonly class AbstractMayBeMergeableStatus extends AbstractStatus
{
    public function mayBeMergeable(): bool
    {
        return true;
    }
}
