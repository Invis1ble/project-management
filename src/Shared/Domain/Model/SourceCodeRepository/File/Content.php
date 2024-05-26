<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File;

use ProjectManagement\Shared\Domain\Model\Str;

final readonly class Content extends Str
{
    public static function fromBase64Encoded(string $encoded): self
    {
        return new self(base64_decode($encoded));
    }
}
