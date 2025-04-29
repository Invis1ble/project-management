<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model;

readonly class NonNegativeInteger extends Integer
{
    public function __construct(int $value)
    {
        $this->validate($value);

        parent::__construct($value);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function validate(int $value): void
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Value must be non-negative.');
        }
    }
}
