<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Service\Utils;

use App\Layer\Domain\Service\Utils\StringUtilsInterface;

final readonly class StringUtils implements StringUtilsInterface
{
    public function truncateString(string $input, int $maxLength): string
    {
        if (mb_strlen($input) > $maxLength) {
            if ($maxLength > 3) {
                return mb_substr($input, 0, $maxLength - 3) . '...';
            }

            return mb_substr($input, 0, $maxLength);
        }

        return $input;
    }

    public function removeHtmlTags(string $input): string
    {
        return strip_tags($input);
    }
}
