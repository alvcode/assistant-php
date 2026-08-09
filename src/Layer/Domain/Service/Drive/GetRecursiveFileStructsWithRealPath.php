<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Drive;

use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\Aggregate\Drive\DriveStructWithRealPathAggregate;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use App\Layer\Domain\ValueObject\PathVO;
use Generator;

/**
 * Сервис принимает на вход structID и базовый реальный путь.
 * Рекурсивно обходит вглубь дерево вложенности папок, возвращая только файлы, возвращая вместе с ними реальный путь,
 * который должен быть до данного файла, если бы он лежал в файловой системе путем сложения basePath который передали
 * и добавления относительного пути самого файла.
 */
final readonly class GetRecursiveFileStructsWithRealPath
{
    public function __construct(
        private FileUtilsInterface $fileUtils,
        private DriveStructRepositoryInterface $driveStructRepository,
    ) {}

    /** @return Generator<DriveStructWithRealPathAggregate> */
    public function service(int $userId, int $structId, string $basePath): Generator
    {
        $structEntity = $this->driveStructRepository->getById($structId, false);
        if ($structEntity->getType() === DriveStructTypeEnum::File) {
            yield new DriveStructWithRealPathAggregate(
                driveStructEntity: $structEntity,
                realPath: new PathVO($this->fileUtils->pathJoin([$basePath, $structEntity->getName()]))
            );
        } else {
            $tree = $this->driveStructRepository->getTreeByUserID($userId, $structId);
            foreach ($tree as $treeStructEntity) {
                if ($treeStructEntity->type === DriveStructTypeEnum::File) {
                    yield new DriveStructWithRealPathAggregate(
                        driveStructEntity: $this->driveStructRepository->getById($treeStructEntity->id, false),
                        realPath: new PathVO(
                            $this->fileUtils->pathJoin([
                                $basePath,
                                $structEntity->getName(),
                                $treeStructEntity->name
                            ])
                        )
                    );
                } else {
                    yield from $this->service(
                        $userId,
                        $treeStructEntity->id,
                        $this->fileUtils->pathJoin([
                            $basePath,
                            $structEntity->getName(),
                        ])
                    );
                }
            }
        }
    }
}
