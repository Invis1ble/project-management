<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

readonly class Name extends Ref
{
    public function relevant(self $name): bool
    {
        return str_starts_with($this->value, $name->value);
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function validate(string $value): void
    {
        parent::validate($value);

        if (preg_match('/(\/\.|\.lock$)/', $value)) {
            throw new \InvalidArgumentException('Branch name contains invalid characters.');
        }

        if (str_contains($value, '..')) {
            throw new \InvalidArgumentException('Branch name contains consecutive dots.');
        }

        if (preg_match('/[ - ~^:?*\[]/', $value)) {
            throw new \InvalidArgumentException('Branch name contains prohibited characters.');
        }

        if (preg_match('/(^\/|\/{2,}|\/$|\.$)/', $value)) {
            throw new \InvalidArgumentException('Branch name has invalid format.');
        }

        if (str_contains($value, '@{') || '@' === $value) {
            throw new \InvalidArgumentException('Branch name contains invalid sequence.');
        }
    }
}
