<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

final readonly class StatusNeedRebase extends AbstractStatus
{
    public function __toString(): string
    {
        return Dictionary::NeedRebase->value;
    }
}
