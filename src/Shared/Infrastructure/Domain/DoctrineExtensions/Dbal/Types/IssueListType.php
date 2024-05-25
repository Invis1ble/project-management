<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use GuzzleHttp\Psr7\Uri;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Board\BoardId;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueId;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\SprintList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Summary;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\TypeId;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\Name;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\Sprint;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\State;

final class IssueListType extends JsonType
{
    public const string NAME = 'issue_list';

    /**
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        try {
            $data = array_map(
                fn (Issue $issue): array => [
                    'id' => $issue->id->value(),
                    'key' => (string) $issue->key,
                    'type_id' => (string) $issue->typeId,
                    'summary' => (string) $issue->summary,
                    'sprints' => array_map(
                        fn (Sprint $sprint): array => [
                            'board_id' => $sprint->boardId->value(),
                            'name' => (string) $sprint->name,
                            'state' => $sprint->state->value,
                        ],
                        $issue->sprints->toArray(),
                    ),
                    'merge_requests' => $this->mergeRequestsToArray($issue->mergeRequests?->toArray()),
                    'merge_requests_to_merge' => $this->mergeRequestsToArray($issue->mergeRequestsToMerge?->toArray()),
                ],
                $value->toArray(),
            );
        } catch (\Throwable $e) {
            throw ConversionException::conversionFailed($value, self::NAME, $e);
        }

        return parent::convertToDatabaseValue($data, $platform);
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): IssueList
    {
        $data = parent::convertToPHPValue($value, $platform);

        try {
            return new IssueList(...array_map(
                fn (array $issue): Issue => new Issue(
                    id: IssueId::from($issue['id']),
                    key: Key::fromString($issue['key']),
                    typeId: TypeId::fromString($issue['type_id']),
                    summary: Summary::fromString($issue['summary']),
                    sprints: new SprintList(...array_map(fn (array $sprint): Sprint => new Sprint(
                        boardId: BoardId::from($sprint['board_id']),
                        name: Name::fromString($sprint['name']),
                        state: State::from($sprint['state']),
                    ), $issue['sprints'])),
                    mergeRequests: $this->createMergeRequests($issue['merge_requests'] ?? null),
                    mergeRequestsToMerge: $this->createMergeRequests($issue['merge_requests_to_merge'] ?? null),
                ),
                $data,
            ));
        } catch (\Throwable $e) {
            throw ConversionException::conversionFailed($value, self::NAME, $e);
        }
    }

    public function getName(): string
    {
        return self::NAME;
    }

    private function createMergeRequests(?array $mergeRequests): ?MergeRequestList
    {
        if (null === $mergeRequests) {
            return null;
        }

        return new MergeRequestList(
            ...array_map(
                fn (array $mr): MergeRequest\MergeRequest => new MergeRequest\MergeRequest(
                    id: MergeRequest\MergeRequestId::from($mr['id']),
                    name: MergeRequest\Name::fromString($mr['name']),
                    projectId: Project\ProjectId::from($mr['project_id']),
                    projectName: Project\Name::fromString($mr['project_name']),
                    sourceBranchName: Branch\Name::fromString($mr['source_branch_name']),
                    targetBranchName: Branch\Name::fromString($mr['target_branch_name']),
                    status: MergeRequest\Status::from($mr['status']),
                    guiUrl: new Uri($mr['gui_url']),
                    details: new Details(
                        status: MergeRequest\Details\Status\StatusFactory::createStatus(
                            MergeRequest\Details\Status\Dictionary::from($mr['details']['status']),
                        ),
                    ),
                ),
                $mergeRequests,
            ),
        );
    }

    private function mergeRequestsToArray(?MergeRequestList $mergeRequests): ?array
    {
        if (null === $mergeRequests) {
            return null;
        }

        return array_map(
            fn (MergeRequest\MergeRequest $mr): array => [
                'id' => $mr->id->value(),
                'name' => (string) $mr->name,
                'project_id' => $mr->projectId->value(),
                'project_name' => (string) $mr->projectName,
                'source_branch_name' => (string) $mr->sourceBranchName,
                'target_branch_name' => (string) $mr->targetBranchName,
                'status' => $mr->status->value,
                'gui_url' => (string) $mr->guiUrl,
                'details' => [
                    'status' => (string) $mr->details->status,
                ],
            ],
            $mergeRequests->toArray(),
        );
    }
}
