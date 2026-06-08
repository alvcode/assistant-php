<?php

declare(strict_types=1);

namespace App\Layer\Domain\ValueObject;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;

final readonly class FileContentVO
{
    public function __construct(
        private string $content,
    ) {}

    public function getContent(): string
    {
        return $this->content;
    }

    public function getFileSize(): FileSizeVO
    {
        return new FileSizeVO(
            strlen($this->content),
            FileSizeTypeEnum::Bytes
        );
    }
}
