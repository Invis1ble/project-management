<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Commit;
use Psr\Http\Message\UriInterface;

final readonly class Branch
{
    public function __construct(
        public Name $name,
        public bool $protected,
        public UriInterface $guiUrl,
        public Commit $commit,
    ) {
    }
}
