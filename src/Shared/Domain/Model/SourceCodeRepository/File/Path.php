<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File;

use Invis1ble\ProjectManagement\Shared\Domain\Model\NonEmptyString;

final readonly class Path extends NonEmptyString
{
    protected function validate(string $value): void
    {
        parent::validate($value);

        if (preg_match('#\.\./#u', $value)) {
            throw new \InvalidArgumentException('Moving to parent directory is forbidden');
        }
    }
}
