<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

use ReleaseManagement\Shared\Domain\Model\NonEmptyString;

readonly class Name extends NonEmptyString
{
    public function equals(self $name): bool
    {
        return $this->value === $name->value;
    }

    public function relevant(self $name): bool
    {
        return str_starts_with($this->value, $name->value);
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function validateName(string $name): void
    {
        // Check for slash-separated components not beginning with a dot or ending with .lock
        if (preg_match('/(\/\.|\.lock$)/', $name)) {
            throw new \InvalidArgumentException('Branch name contains invalid characters.');
        }

        // Check for consecutive dots
        if (str_contains($name, '..')) {
            throw new \InvalidArgumentException('Branch name contains consecutive dots.');
        }

        // Check for prohibited characters
        if (preg_match('/[ - ~^:?*\[]/', $name)) {
            throw new \InvalidArgumentException('Branch name contains prohibited characters.');
        }

        // Check for leading or trailing slash, multiple consecutive slashes, or ending with dot
        if (preg_match('/(^\/|\/{2,}|\/$|\.$)/', $name)) {
            throw new \InvalidArgumentException('Branch name has invalid format.');
        }

        // Check for sequence @{ and single character @
        if (str_contains($name, '@{') || $name === '@') {
            throw new \InvalidArgumentException('Branch name contains invalid sequence.');
        }
    }
}
