<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File;

use ProjectManagement\Shared\Domain\Model\NonEmptyString;

final readonly class FilePath extends NonEmptyString
{
    protected function validate(string $value): void
    {
        parent::validate($value);

        if (preg_match('#\.\./#u', $value)) {
            throw new \InvalidArgumentException('Moving to parent directory is forbidden');
        }
    }

    public function equals(self $path): bool
    {
        return $path->value === $this->value;
    }
}
