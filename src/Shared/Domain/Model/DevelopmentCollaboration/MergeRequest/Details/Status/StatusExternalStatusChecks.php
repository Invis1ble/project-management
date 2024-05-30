<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

final readonly class StatusExternalStatusChecks extends AbstractStatus
{
    public function __toString(): string
    {
        return Dictionary::ExternalStatusChecks->value;
    }
}
