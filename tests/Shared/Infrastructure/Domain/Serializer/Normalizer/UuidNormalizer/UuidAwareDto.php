<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\UuidNormalizer;

final readonly class UuidAwareDto
{
    public function __construct(
        public TestUuid $uuid,
    ) {
    }
}
