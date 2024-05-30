<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final readonly class BranchFactory implements BranchFactoryInterface
{
    public function __construct(
        private CommitFactoryInterface $commitFactory,
        private UriFactoryInterface $uriFactory,
    ) {
    }

    public function createBranch(
        string $name,
        bool $protected,
        string $guiUrl,
        string $commitId,
        ?string $commitMessage,
        string $commitCreatedAt,
    ): Branch {
        return new Branch(
            name: Name::fromString($name),
            protected: $protected,
            guiUrl: $this->uriFactory->createUri($guiUrl),
            commit: $this->commitFactory->createCommit($commitId, $commitMessage, $commitCreatedAt),
        );
    }
}
