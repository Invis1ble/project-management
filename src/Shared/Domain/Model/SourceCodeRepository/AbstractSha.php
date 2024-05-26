<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository;

use ProjectManagement\Shared\Domain\Model\NonEmptyString;

abstract readonly class AbstractSha extends NonEmptyString
{
    protected function validate(string $value): void
    {
        parent::validate($value);

        if (!preg_match('/^[[:xdigit:]]{40}$/', $value)) {
            throw new \InvalidArgumentException('Hash is not valid');
        }
    }
}
