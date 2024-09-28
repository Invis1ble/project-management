<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model;

/**
 * @template T
 */
abstract readonly class AbstractList implements \IteratorAggregate, \Countable
{
    public function empty(): bool
    {
        return 0 === iterator_count($this->elements());
    }

    /**
     * @return \Generator<T>
     */
    public function getIterator(): \Generator
    {
        yield from $this->elements();
    }

    public function count(): int
    {
        return iterator_count($this->elements());
    }

    /**
     * @param callable(T): bool $predicate
     */
    public function filter(callable $predicate): static
    {
        return new static(
            ...(function (callable $predicate): iterable {
                foreach ($this->elements() as $element) {
                    if ($predicate($element)) {
                        yield $element;
                    }
                }
            })($predicate),
        );
    }

    /**
     * @template R
     *
     * @param callable(T): R $callback
     */
    public function map(callable $callback): \Generator
    {
        foreach ($this->elements() as $element) {
            yield $callback($element);
        }
    }

    /**
     * @param callable(T): bool $predicate
     */
    public function exists(callable $predicate): bool
    {
        foreach ($this->elements() as $element) {
            if ($predicate($element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template R
     *
     * @param callable(?R, T): ?R $callback
     * @param ?R                  $initial
     *
     * @return ?R
     */
    public function reduce(callable $callback, $initial = null)
    {
        foreach ($this->elements() as $element) {
            $initial = $callback($initial, $element);
        }

        return $initial;
    }

    /**
     * @template R
     *
     * @return \Generator<R>
     */
    public function pluck(string $propertyName): \Generator
    {
        foreach ($this->elements() as $element) {
            yield $element->$propertyName;
        }
    }

    /**
     * @return array<T>
     */
    public function toArray(): array
    {
        return iterator_to_array($this->elements());
    }

    public function equals(self $other): bool
    {
        if (static::class !== $other::class || $this->count() !== $other->count()) {
            return false;
        }

        $otherElements = $other->toArray();

        foreach ($this->toArray() as $k => $element) {
            if (!isset($otherElements[$k])) {
                return false;
            }

            if (!$this->elementsEquals($element, $otherElements[$k])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return iterable<T>
     */
    abstract protected function elements(): iterable;

    /**
     * @param T $element1
     * @param T $element2
     */
    abstract protected function elementsEquals($element1, $element2): bool;
}
