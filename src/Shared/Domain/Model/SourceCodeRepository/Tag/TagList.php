<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractList;

/**
 * @extends AbstractList<Tag>
 */
final readonly class TagList extends AbstractList
{
    private iterable $elements;

    public function __construct(Tag ...$tags)
    {
        $this->elements = $tags;
    }

    public function append(Tag $tag): self
    {
        return new self(
            ...(function (Tag $tag): iterable {
                yield from $this->elements;
                yield $tag;
            })($tag),
        );
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
