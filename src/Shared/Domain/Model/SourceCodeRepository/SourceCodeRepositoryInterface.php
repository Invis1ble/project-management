<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\VersionName;

interface SourceCodeRepositoryInterface
{
    public function createBranch(
        Branch\Name $name,
        Ref $ref,
    ): Branch\Branch;

    /**
     * @return Tag\Tag<VersionName>
     */
    public function createTag(
        Tag\Name $name,
        Ref $ref,
        ?Tag\Message $message = null,
    ): Tag\Tag;

    public function commit(
        Branch\Name $branchName,
        Commit\Message $message,
        NewCommit\Action\ActionList $actions,
    ): Commit\Commit;

    /**
     * @return ?Tag\Tag<VersionName>
     */
    public function latestTagToday(): ?Tag\Tag;

    public function file(
        Branch\Name $branchName,
        File\FilePath $filePath,
    ): File\File;
}
