<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\SourceCodeRepository\Diff;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Diff\CompareResult;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Diff\CompareResultFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Diff\Content;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Diff\Diff;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Diff\DiffList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Path;
use Psr\Http\Message\UriFactoryInterface;

final readonly class CompareResultFactory implements CompareResultFactoryInterface
{
    public function __construct(
        private CommitFactoryInterface $commitFactory,
        private UriFactoryInterface $uriFactory,
    ) {
    }

    public function createCompareResult(
        ?array $commit,
        array $commits,
        array $diffs,
        bool $compareTimout,
        bool $compareSameRef,
        string $guiUrl,
    ): CompareResult {
        if (null === $commit) {
            $commit = null;
        } else {
            $commit = $this->commitFactory->createCommit(
                id: $commit['id'],
                message: $commit['message'] ?? null,
                createdAt: $commit['created_at'],
            );
        }

        return new CompareResult(
            commit: $commit,
            commits: new CommitList(...array_map(
                callback: fn (array $commit): Commit => $this->commitFactory->createCommit(
                    id: $commit['id'],
                    message: $commit['message'] ?? null,
                    createdAt: $commit['created_at'],
                ),
                array: $commits,
            )),
            diffs: new DiffList(...array_map(
                callback: fn (array $diff): Diff => new Diff(
                    oldPath: Path::fromString($diff['old_path']),
                    newPath: Path::fromString($diff['new_path']),
                    content: Content::fromString($diff['diff']),
                    newFile: $diff['new_file'],
                    renamedFile: $diff['renamed_file'],
                    deletedFile: $diff['deleted_file'],
                ),
                array: $diffs,
            )),
            compareTimeout: $compareTimout,
            compareSameRef: $compareSameRef,
            guiUrl: $this->uriFactory->createUri($guiUrl),
        );
    }
}
