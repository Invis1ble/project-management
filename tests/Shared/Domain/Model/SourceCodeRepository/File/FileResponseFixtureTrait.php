<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\SourceCodeRepository\File;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\File;

trait FileResponseFixtureTrait
{
    public function fileResponseFixture(
        File\Content $content,
    ): array {
        $file = file_get_contents(__DIR__ . '/fixture/response/file.200.json');
        $file = json_decode($file, true);

        return [
            'content' => base64_encode((string) $content),
            'size' => strlen((string) $content),
        ] + $file;
    }
}
