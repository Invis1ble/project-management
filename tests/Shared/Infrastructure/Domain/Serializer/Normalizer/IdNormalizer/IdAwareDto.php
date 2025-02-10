<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\IdNormalizer;

final readonly class IdAwareDto
{
    public function __construct(
        public TestId $id,
    ) {
    }
}
