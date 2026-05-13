<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Utils;

use App\Layer\Domain\Entity\UserEntity;
use Random\RandomException;

interface HasherServiceInterface
{
    public function hashUserPassword(string $password): string;

    public function isUserPasswordValid(UserEntity $user, string $password): bool;

    /**
     * @throws RandomException
     */
    public function generateRandomString(int $length = 32): string;

    /**
     * Генерация случайно строки на основе криптографической функции random_bytes
     * без спец.символов
     */
    public function generateRandomStringWithoutSymbols(int $length = 32): string;
}
