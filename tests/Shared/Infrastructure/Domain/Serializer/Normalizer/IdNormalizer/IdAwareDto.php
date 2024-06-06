<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\IdNormalizer;

use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractId;

final readonly class IdAwareDto
{
    public function __construct(
        public AbstractId $abstract,
        public TestId $concrete,
    ) {
    }
}
