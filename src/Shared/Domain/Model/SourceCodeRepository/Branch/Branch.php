<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Commit;
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
