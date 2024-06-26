<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File;

use Invis1ble\ProjectManagement\Shared\Domain\Model\String_;

final readonly class Content extends String_
{
    public static function fromBase64Encoded(string $encoded): self
    {
        return new self(base64_decode($encoded));
    }
}
