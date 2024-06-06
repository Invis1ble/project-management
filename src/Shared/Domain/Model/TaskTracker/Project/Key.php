<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;

use Invis1ble\ProjectManagement\Shared\Domain\Model\NonEmptyString;

final readonly class Key extends NonEmptyString
{
    protected function validate(string $value): void
    {
        if (!preg_match('/^[A-Z][A-Z]+$/', $value)) {
            throw new \InvalidArgumentException("Invalid project key provided: $value");
        }
    }
}
