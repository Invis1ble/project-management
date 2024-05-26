<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

final readonly class StatusCiMustPass extends AbstractStatus
{
    public function __toString(): string
    {
        return Dictionary::CiMustPass->value;
    }
}
