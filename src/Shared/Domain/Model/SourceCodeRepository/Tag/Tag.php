<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Commit;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitId;

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
}
