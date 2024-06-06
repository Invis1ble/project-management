<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;

use Invis1ble\ProjectManagement\Shared\Domain\Model\NonEmptyString;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

final readonly class Key extends NonEmptyString
{
    public function toBranchName(): Name
    {
        return Name::fromString($this->value);
    }

    protected function validate(string $value): void
    {
        if (!preg_match('/^[A-Z][A-Z]+-[0-9]+$/', $value)) {
            throw new \InvalidArgumentException("Invalid issue key provided: $value");
        }
    }
}
