<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action;

use ProjectManagement\Shared\Domain\Model\AbstractList;

/**
 * @extends AbstractList<AbstractAction>
 */
final readonly class ActionList extends AbstractList
{
    private iterable $elements;

    public function __construct(AbstractAction ...$actions)
    {
        if (0 === iterator_count($actions)) {
            throw new \InvalidArgumentException('Actions must have at least one action');
        }

        $this->elements = $actions;
    }

    protected function elements(): iterable
    {
        return $this->elements;
    }
}
