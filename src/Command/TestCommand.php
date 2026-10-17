<?php

namespace App\Command;

use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use Generator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'test',
    description: 'Command for test',
)]
class TestCommand extends Command
{
    private const STRUCTS = [
        [
            'id' => 1,
            'name' => 'folder 1',
            'type' => 0,
            'parent_id' => null,
        ],
        [
            'id' => 2,
            'name' => 'folder 2',
            'type' => 0,
            'parent_id' => 1,
        ],
        [
            'id' => 5,
            'name' => 'file1.pdf',
            'type' => 1,
            'parent_id' => 2,
        ],
        [
            'id' => 6,
            'name' => 'file2.pdf',
            'type' => 1,
            'parent_id' => 2,
        ],
        [
            'id' => 3,
            'name' => 'folder 3',
            'type' => 0,
            'parent_id' => 2,
        ],
        [
            'id' => 4,
            'name' => '99px_ru_wallpaper_349178_fioletovokrasnie_skladki_na_temnom_fone_dlja_windows_11.jpg',
            'type' => 1,
            'parent_id' => 3,
        ]
    ];

    public function __construct(
        private FileUtilsInterface $fileUtils,
        private ConfigRepositoryInterface $configRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $archiveDirectory = $this->fileUtils->pathJoin([
            $this->configRepository->getTempSavePath(),
            'archives',
            1
        ]);

        foreach ($this->getRecursiveWithPath(1, $archiveDirectory) as $struct) {
            $io->text('-----');
            $io->text('path: ' . $struct['path']);
            $io->text('name: ' . $struct['name']);
        }

        $io->success('end');

        return Command::SUCCESS;
    }

    private function getById(int $id): ?array
    {
        foreach (self::STRUCTS as $struct) {
            if ($struct['id'] === $id) {
                return $struct;
            }
        }
        return null;
    }

    private function getTreeByParentId(int $parentId): array
    {
        $tree = [];
        foreach (self::STRUCTS as $struct) {
            if ($struct['parent_id'] === $parentId) {
                $tree[] = $struct;
            }
        }
        return $tree;
    }

    public function getRecursiveWithPath(int $structId, string $basePath): Generator
    {
        $structEntity = $this->getById($structId);
        if ($structEntity['type'] === 1) {
            $structEntity['path'] = $this->fileUtils->pathJoin([$basePath, $structEntity['name']]);
            yield $structEntity;
        } else {
            $tree = $this->getTreeByParentId($structId);
            foreach ($tree as $key => $treeStructEntity) {
                if ($treeStructEntity['type'] === 1) {
                    $tree[$key]['path'] = $this->fileUtils->pathJoin([
                        $basePath,
                        $structEntity['name'],
                        $treeStructEntity['name']
                    ]);
                    yield $tree[$key];
                } else {
                    yield from $this->getRecursiveWithPath(
                        $treeStructEntity['id'],
                        $this->fileUtils->pathJoin([
                            $basePath,
                            $structEntity['name'],
                        ])
                    );
                }
            }
        }
    }
}
