<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Diff;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitList;
use Psr\Http\Message\UriInterface;

final readonly class CompareResult
{
    public function __construct(
        public ?Commit $commit,
        public CommitList $commits,
        public DiffList $diffs,
        public bool $compareTimeout,
        public bool $compareSameRef,
        public UriInterface $guiUrl,
    ) {
    }

    public function diffsEmpty(): bool
    {
        return $this->diffs->empty();
    }

    public function equals(self $other): bool
    {
        if (null === $this->commit) {
            if (null !== $other->commit) {
                return false;
            }
        } elseif (null === $other->commit) {
            return false;
        } elseif (!$this->commit->equals($other->commit)) {
            return false;
        }

        return $this->commits->equals($other->commits)
            && $this->diffs->equals($other->diffs)
            && $this->compareTimeout === $other->compareTimeout
            && $this->compareSameRef === $other->compareSameRef
            && (string) $this->guiUrl === (string) $other->guiUrl
        ;
    }
}
