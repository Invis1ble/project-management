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

    public function equals(self $element2): bool
    {
        return $this->commit->equals($element2->commit)
            && $this->name->equals($element2->name)
            && $this->target->equals($element2->target)
            && $this->message?->equals($element2->message)
            && $this->createdAt === $element2->createdAt
        ;
    }
}
