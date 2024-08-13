<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitId;

/**
 * @template T of Name
 */
final readonly class Tag
{
    /**
     * @param T $name
     */
    public function __construct(
        public Commit $commit,
        public Name $name,
        public CommitId $target,
        public ?Message $message,
        public ?\DateTimeImmutable $createdAt,
    ) {
    }

    public function equals(self $other): bool
    {
        if (null === $this->message) {
            if (null !== $other->message) {
                return false;
            }
        } elseif (null === $other->message) {
            return false;
        } elseif (!$this->message->equals($other->message)) {
            return false;
        }

        return $this->commit->equals($other->commit)
            && $this->name->equals($other->name)
            && $this->target->equals($other->target)
            // phpcs:disable Symfony.ControlStructure.IdenticalComparison.Warning
            && $this->createdAt == $other->createdAt
            // phpcs:enable Symfony.ControlStructure.IdenticalComparison.Warning
        ;
    }
}
