<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model;

readonly class Integer
{
    public function __construct(public int $value)
    {
    }
}
