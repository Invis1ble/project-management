<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File;

use ProjectManagement\Shared\Domain\Model\NonEmptyString;

final readonly class Filename extends NonEmptyString
{
    protected function validate(string $value): void
    {
        parent::validate($value);

        if (str_contains($value, '/')) {
            throw new \InvalidArgumentException('Filename cannot contain slashes.');
        }
    }
}
