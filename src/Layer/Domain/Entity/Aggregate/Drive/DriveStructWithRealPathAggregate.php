<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity\Aggregate\Drive;

use App\Layer\Domain\Entity\DriveStructEntity;
use App\Layer\Domain\ValueObject\PathVO;

final readonly class DriveStructWithRealPathAggregate
{
    public function __construct(
        public DriveStructEntity $driveStructEntity,
        public PathVO $realPath,
    ) {}
}
