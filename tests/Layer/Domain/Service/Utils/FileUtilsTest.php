<?php

declare(strict_types=1);

namespace App\Tests\Layer\Domain\Service\Utils;

use App\Layer\Infrastructure\Service\Utils\FileUtils;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FileUtilsTest extends KernelTestCase
{
    public function testMiddlePathByFileID()
    {
        $testCases = [
            ['fileID' => 0, 'expected' => '1/1/'],
            ['fileID' => 1, 'expected' => '1/1/'],
            ['fileID' => 10, 'expected' => '1/1/'],
            ['fileID' => 998, 'expected' => '1/1/'],
            ['fileID' => 999, 'expected' => '1/1/'],
            ['fileID' => 1000, 'expected' => '1/2/'],
            ['fileID' => 1001, 'expected' => '1/2/'],
            ['fileID' => 1999, 'expected' => '1/2/'],
            ['fileID' => 2000, 'expected' => '1/3/'],
            ['fileID' => 2001, 'expected' => '1/3/'],
            ['fileID' => 19998, 'expected' => '1/20/'],
            ['fileID' => 19999, 'expected' => '1/20/'],
            ['fileID' => 20000, 'expected' => '1/21/'],
            ['fileID' => 20001, 'expected' => '1/21/'],
            ['fileID' => 200000, 'expected' => '1/201/'],
            ['fileID' => 200001, 'expected' => '1/201/'],
            ['fileID' => 999998, 'expected' => '1/1000/'],
            ['fileID' => 999999, 'expected' => '1/1000/'],
            ['fileID' => 1000000, 'expected' => '2/1/'],
            ['fileID' => 1000001, 'expected' => '2/1/']
        ];

        /** @var FileUtils $fileUtils */
        $fileUtils = self::getContainer()->get(FileUtils::class);

        foreach ($testCases as $case) {
            $result = $fileUtils->getMiddlePathByFileID($case['fileID']);
            $this->assertSame(
                $case['expected'],
                $result,
                sprintf(
                    "Case fileID: %s / Expected: %s / Fact: %s",
                    $case['fileID'],
                    $case['expected'],
                    $result
                )
            );
        }
    }

    public function testEncryptDecryptFile()
    {
        $content = "Текст, который будет зашифрован в файле";

        /** @var FileUtils $fileUtils */
        $fileUtils = self::getContainer()->get(FileUtils::class);

        $tempPath = $fileUtils->createTempFile();
        file_put_contents($tempPath, $content);

        $encryptedFile = $fileUtils->encryptFile(new \SplFileInfo($tempPath), 'key');

        $this->assertFileExists($encryptedFile->getPathname());

        $this->assertNotEquals(
            $content,
            file_get_contents($encryptedFile->getPathname())
        );

        $decryptedFile = $fileUtils->decryptFile($encryptedFile, 'key');
        $this->assertFileExists($decryptedFile->getPathname());
        $this->assertEquals(
            $content,
            file_get_contents($decryptedFile->getPathname())
        );
    }

    public function testPathJoin()
    {
        $relativeTestCases = [
            ['path1' => 'folder', 'path2' => 'folder1', 'expected' => 'folder/folder1'],
            ['path1' => 'folder/', 'path2' => 'folder1', 'expected' => 'folder/folder1'],
            ['path1' => 'folder//', 'path2' => 'folder1', 'expected' => 'folder/folder1'],
            ['path1' => 'folder///', 'path2' => 'folder1', 'expected' => 'folder/folder1'],
            ['path1' => 'folder///', 'path2' => 'folder1/', 'expected' => 'folder/folder1'],
            ['path1' => 'folder///', 'path2' => 'folder1//', 'expected' => 'folder/folder1'],
            ['path1' => '/folder///', 'path2' => '/folder1//', 'expected' => 'folder/folder1'],
        ];
        $absoluteTestCases = [
            ['path1' => 'folder', 'path2' => 'folder1', 'expected' => '/folder/folder1'],
            ['path1' => 'folder/', 'path2' => 'folder1', 'expected' => '/folder/folder1'],
            ['path1' => 'folder//', 'path2' => 'folder1', 'expected' => '/folder/folder1'],
            ['path1' => 'folder///', 'path2' => 'folder1', 'expected' => '/folder/folder1'],
            ['path1' => 'folder///', 'path2' => 'folder1/', 'expected' => '/folder/folder1'],
            ['path1' => 'folder///', 'path2' => 'folder1//', 'expected' => '/folder/folder1'],
            ['path1' => '/folder///', 'path2' => '/folder1//', 'expected' => '/folder/folder1'],
        ];

        /** @var FileUtils $fileUtils */
        $fileUtils = self::getContainer()->get(FileUtils::class);

        foreach ($relativeTestCases as $case) {
            $result = $fileUtils->pathJoin([$case['path1'], $case['path2']]);
            $this->assertSame(
                $case['expected'],
                $result,
                sprintf(
                    "Case path1-path2: %s-%s / Expected: %s / Fact: %s",
                    $case['path1'],
                    $case['path2'],
                    $case['expected'],
                    $result
                )
            );
        }
        foreach ($absoluteTestCases as $case) {
            $result = $fileUtils->pathJoin([$case['path1'], $case['path2']], true);
            $this->assertSame(
                $case['expected'],
                $result,
                sprintf(
                    "Case path1-path2: %s-%s / Expected: %s / Fact: %s",
                    $case['path1'],
                    $case['path2'],
                    $case['expected'],
                    $result
                )
            );
        }
    }
}
