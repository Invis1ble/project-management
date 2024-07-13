<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\SourceCodeRepository\Tag;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

trait CreateTagResponseFixtureTrait
{
    public function createTagResponseFixture(
        Tag\Name $tagName,
        Tag\Message $tagMessage,
        Commit\Message $commitMessage,
        \DateTimeImmutable $commitCreatedAt,
        \DateTimeImmutable $tagCreatedAt,
    ): array {
        $tag = file_get_contents(__DIR__ . '/fixture/response/create_tag.200.json');
        $tag = json_decode($tag, true);

        return [
            'name' => (string) $tagName,
            'commit' => [
                'message' => (string) $commitMessage,
                'created_at' => $commitCreatedAt->format(DATE_RFC3339_EXTENDED),
            ] + $tag['commit'],
            'message' => (string) $tagMessage,
            'created_at' => $tagCreatedAt->format(DATE_RFC3339_EXTENDED),
        ] + $tag;
    }
}
