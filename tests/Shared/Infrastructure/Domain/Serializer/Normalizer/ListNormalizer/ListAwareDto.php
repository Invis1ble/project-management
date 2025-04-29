<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\ListNormalizer;

final readonly class ListAwareDto
{
    public function __construct(
        public TestList $list,
    ) {
    }
}
