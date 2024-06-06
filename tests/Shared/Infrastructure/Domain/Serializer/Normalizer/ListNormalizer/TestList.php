<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\ListNormalizer;

use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\IdNormalizer\TestId;

final readonly class TestList extends AbstractTestList
{
    public function __construct(TestId ...$ids)
    {
        parent::__construct(...$ids);
    }
}
