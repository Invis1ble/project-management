<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\SourceCodeRepository\NewCommit;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Message;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\Content;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;
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

    public function createSetFrontendApplicationBranchNameCommit(Name $branchName): ?NewCommit
    {
        $configFilePath = FilePath::fromString('.helm/values.yaml');

        $file = $this->backendSourceCodeRepository->file(Name::fromString('develop'), $configFilePath);

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
            message: Message::fromString("Change frontend application branch name to $branchName"),
            actions: new ActionList(
                new ActionUpdate(
                    filePath: $configFilePath,
                    content: Content::fromString($config),
                ),
            ),
        );
    }
}
