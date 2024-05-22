<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model;

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
     * @return array<T>
     */
    public function toArray(): array
    {
        return iterator_to_array($this->elements());
    }

    /**
     * @return iterable<T>
     */
    abstract protected function elements(): iterable;
}
