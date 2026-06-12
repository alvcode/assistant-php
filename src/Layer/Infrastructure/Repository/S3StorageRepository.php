<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\Repository\StorageRepositoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use Aws\S3\S3Client;
use Exception;
use SplFileInfo;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final readonly class S3StorageRepository implements StorageRepositoryInterface
{
    private S3Client $client;

    public function __construct(
        private ParameterBagInterface $parameterBag,
        private FileUtilsInterface $fileUtils,
    ) {
        $this->client = new S3Client([
            'version' => 'latest',
            'use_path_style_endpoint' => true,
            'region' => $this->parameterBag->get('s3.location'),
            'endpoint' => $this->parameterBag->get('s3.endpoint'),
            'credentials' => [
                'key' => $this->parameterBag->get('s3.accessKey'),
                'secret' => $this->parameterBag->get('s3.secretAccessKey'),
            ],
        ]);
    }

    public function save(SaveFileDTO $in): void
    {
        $this->client->putObject([
            'Bucket' => $this->parameterBag->get('s3.bucketName'),
            'Key' => $in->getSavePath(),
            'Body' => fopen($in->getFile()->getRealPath(), 'rb'),
        ]);
    }

    /**
     * @throws Exception
     */
    public function getFile(string $path): SplFileInfo
    {
        $tempFile = null;
        $handle = null;
        $bodyStream = null;

        try {
            $tempFile = $this->fileUtils->createTempFile();
            $handle = fopen($tempFile, 'wb');

            if ($handle === false) {
                throw new Exception('Не удалось создать временный файл');
            }

            $result = $this->client->getObject([
                'Bucket' => $this->parameterBag->get('s3.bucketName'),
                'Key' => $path,
                '@stream' => true
            ]);

            $bodyStream = $result['Body'];

            while (!$bodyStream->eof()) {
                $chunk = $bodyStream->read(1024 * 1024); // по 1MB
                fwrite($handle, $chunk);
            }

            return new SplFileInfo($tempFile);
        } catch (Exception $e) {
            throw new Exception(sprintf('Failed to get file from S3: %s', $e->getMessage()), $e->getCode(), $e);
        } finally {
            if ($handle !== null) {
                fclose($handle);
            }
            if ($bodyStream !== null) {
                $bodyStream->close();
            }
        }
    }
}
