<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\SourceCodeRepository\Commit;

trait CreateCommitResponseFixtureTrait
{
    public function createCommitResponseFixture(
        \DateTimeImmutable $createdAt,
    ): array {
        $commit = file_get_contents(__DIR__ . '/fixture/response/create_commit.200.json');
        $commit = json_decode($commit, true);

        return [
            'message' => 'Change frontend application branch name to develop',
            'created_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
        ] + $commit;
    }
}
