<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

interface TagFactoryInterface
{
    public function createTag(
        string $name,
        string $commitId,
        ?string $commitMessage,
        string $commitCreatedAt,
        string $target,
        ?string $message,
        ?string $createdAt,
    ): Tag;
}
