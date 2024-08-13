<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\SourceCodeRepository\NewCommit;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionUpdate;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\NewCommit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class SetFrontendApplicationBranchNameCommitFactory implements SetFrontendApplicationBranchNameCommitFactoryInterface
{
    public function __construct(
        private ProjectId $backendProjectId,
        private SourceCodeRepositoryInterface $backendSourceCodeRepository,
    ) {
    }

    public function createSetFrontendApplicationBranchNameCommit(Branch\Name $branchName): ?NewCommit
    {
        $configFilePath = File\Path::fromString('.helm/values.yaml');

        $file = $this->backendSourceCodeRepository->file(Branch\Name::fromString('develop'), $configFilePath);

        $config = preg_replace(
            pattern: '/(Deploy_react:\s*host:\s*_default:\s*)"[^"]+"/',
            replacement: "\$1\"$branchName\"",
            subject: (string) $file->content,
        );

        if ((string) $file->content === $config) {
            return null;
        }

        return new NewCommit(
            projectId: $this->backendProjectId,
            branchName: $branchName,
            message: Commit\Message::fromString("Change frontend application branch name to $branchName"),
            actions: new ActionList(
                new ActionUpdate(
                    filePath: $configFilePath,
                    content: File\Content::fromString($config),
                ),
            ),
        );
    }
}
