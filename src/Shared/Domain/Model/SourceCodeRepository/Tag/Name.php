<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Domain\Model\String_;

readonly class Name extends Ref
{
    public function equals(String_ $other): bool
    {
        return $other instanceof self
            && $other->value === $this->value
        ;
    }
}
