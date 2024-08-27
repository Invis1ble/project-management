<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition;

use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractList;

/**
 * @extends AbstractList<Transition>
 */
final readonly class TransitionList extends AbstractList
{
    private iterable $elements;

    public function __construct(Transition ...$transitions)
    {
        $this->elements = $transitions;
    }

    public function get(Name $name): Transition
    {
        foreach ($this->elements as $element) {
            if ($name->equals($element->name)) {
                return $element;
            }
        }

        throw new \RuntimeException("Transition '$name' not found");
    }

    protected function elements(): iterable
    {
        return $this->elements;
    }

    protected function elementsEquals($element1, $element2): bool
    {
        return $element1::class === $element2::class
            && $element1->equals($element2)
        ;
    }
}
