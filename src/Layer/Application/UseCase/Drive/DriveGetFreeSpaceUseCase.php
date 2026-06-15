<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\DTO\Drive\DriveGetFreeSpaceDTO;
use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\ValueObject\FileSizeVO;

final readonly class DriveGetFreeSpaceUseCase
{
    public function __construct(
        private DriveFileRepositoryInterface $driveFileRepository,
        private ConfigRepositoryInterface $configRepository,
    ) {}

    public function handle(int $userId): DriveGetFreeSpaceDTO
    {
        return new DriveGetFreeSpaceDTO(
            total: new FileSizeVO($this->configRepository->getDriveStorageLimitPerUser(), FileSizeTypeEnum::Bytes),
            used: $this->driveFileRepository->getUsedSpaceByUserID($userId),
        );
    }
}