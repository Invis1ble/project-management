<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model\SourceCodeRepository\NewCommit;

use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Commit\Message;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\File\Content;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\File\FilePath;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionList;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionUpdate;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\NewCommit;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class SetFrontendApplicationBranchNameCommitFactory implements SetFrontendApplicationBranchNameCommitFactoryInterface
{
    public function __construct(
        private ProjectId $backendProjectId,
        private SourceCodeRepositoryInterface $backendSourceCodeRepository,
    ) {
    }

    public function createSetFrontendApplicationBranchNameCommit(
        Name $targetBranchName,
        ?Name $startBranchName = null,
    ): NewCommit {
        $configFilePath = FilePath::fromString('.helm/values.yaml');

        $file = $this->backendSourceCodeRepository->file(Name::fromString('develop'), $configFilePath);

        $config = preg_replace(
            pattern: '/(Deploy_react:\s*host:\s*_default:\s*)"[^"]+"/',
            replacement: "\$1\"$targetBranchName\"",
            subject: (string) $file->content,
        );

        return new NewCommit(
            projectId: $this->backendProjectId,
            branchName: $targetBranchName,
            message: Message::fromString("Change frontend application branch name to $targetBranchName"),
            actions: new ActionList(
                new ActionUpdate(
                    filePath: $configFilePath,
                    content: Content::fromString($config),
                ),
            ),
            startBranchName: Name::fromString('develop'),
        );
    }
}
