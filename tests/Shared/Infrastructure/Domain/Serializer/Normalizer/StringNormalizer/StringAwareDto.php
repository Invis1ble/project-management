<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\StringNormalizer;

final readonly class StringAwareDto
{
    public function __construct(
        public TestString $string,
    ) {
    }
}
