<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\SourceCodeRepository;

use ReleaseManagement\Shared\Domain\Model\NonEmptyString;

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
