<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Utils;

final readonly class FileUtils
{
    public function __construct(
        private HasherServiceInterface $hasherService,
    ) {}

    public function generateNewFilename(string $extension): string
    {
        return sprintf(
            "%d_%s.%s",
            time(),
            $this->hasherService->generateRandomStringWithoutSymbols(10),
            $extension
        );
    }

    public function getMiddlePathByFileID(int $fileID): string
    {
        $dirLevel1 = $fileID / 1_000_000;
        $dirLevel2 = ($fileID % 1_000_000) / 1_000;
        return sprintf("%d/%d/", $dirLevel1+1, $dirLevel2+1);
    }

    /** @param string[] $parts */
    function pathJoin(array $parts, bool $isAbsolute = false): string
    {
        $parts = array_map(
            static fn(string $part): string => trim($part, '/\\'),
            $parts
        );
        $res = implode(DIRECTORY_SEPARATOR, $parts);
        if ($isAbsolute) {
            $res = '/' . $res;
        }
        return $res;
    }
}
