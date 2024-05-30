<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository;

use Invis1ble\ProjectManagement\Shared\Domain\Model\NonEmptyString;

readonly class Ref extends NonEmptyString
{
    protected function validate(string $value): void
    {
        $components = explode('/', $value);
        foreach ($components as $component) {
            if ('' === $component || '.' === $component[0]) {
                throw new \InvalidArgumentException('No component of the ref can begin with a dot.');
            }
            if (str_ends_with($component, '.lock')) {
                throw new \InvalidArgumentException("No component of the ref can end with '.lock'.");
            }
        }

        if (str_contains($value, '..')) {
            throw new \InvalidArgumentException("Ref cannot contain consecutive dots '..'.");
        }

        if (preg_match('/[\x00-\x1F\x7F ~^:]/', $value)) {
            throw new \InvalidArgumentException("Ref cannot contain ASCII control characters, space, tilde '~', caret '^', or colon ':'.");
        }

        if (preg_match('/[?*\[]/', $value)) {
            throw new \InvalidArgumentException("Ref cannot contain '?', '*', or '['.");
        }

        if ('/' === $value[0] || str_ends_with($value, '/') || str_contains($value, '//')) {
            throw new \InvalidArgumentException("Ref cannot begin or end with a slash '/' or contain multiple consecutive slashes.");
        }

        if (str_ends_with($value, '.')) {
            throw new \InvalidArgumentException("Ref cannot end with a dot '.'.");
        }

        if (str_contains($value, '@{')) {
            throw new \InvalidArgumentException("Ref cannot contain the sequence '@{'.");
        }

        if ('@' === $value) {
            throw new \InvalidArgumentException("Ref cannot be the single character '@'.");
        }

        if (str_contains($value, '\\')) {
            throw new \InvalidArgumentException("Ref cannot contain a backslash '\\'.");
        }
    }
}
