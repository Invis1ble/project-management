<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\ListNormalizer;

use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractList;

/**
 * @extends AbstractList<AbstractId>
 */
abstract readonly class AbstractTestList extends AbstractList
{
    private iterable $elements;

    public function __construct(AbstractId ...$ids)
    {
        $this->elements = $ids;
    }

    protected function elements(): iterable
    {
        return $this->elements;
    }

    protected function elementsEquals($element1, $element2): bool
    {
        return $element1::class === $element2::class
            && $element1->equals($element2);
    }
}
