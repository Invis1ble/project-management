<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;

use Psr\Http\Message\UriInterface;

final readonly class Commit
{
    public function __construct(
        public CommitId $id,
        public Message $message,
        public UriInterface $guiUrl,
    ) {
    }
}
