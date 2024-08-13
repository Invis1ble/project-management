<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;

use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractList;

/**
 * @extends AbstractList<Commit>
 */
final readonly class CommitList extends AbstractList
{
    private iterable $elements;

    public function __construct(Commit ...$commits)
    {
        $this->elements = $commits;
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
