<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Service\Utils;

use App\Layer\Domain\Entity\UserEntity;
use App\Layer\Domain\Service\Utils\HasherServiceInterface;
use App\Layer\Infrastructure\DTO\User\UserPasswordDTO;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class HasherService implements HasherServiceInterface
{
    private const BREAK_WORDS = [
        'fuck', 'sex', 'love', 'dick', 'slave', 'master', 'bitch', 'pay', 'war', 'whore', 'anal',
        'boobs', 'tits', 'ass', 'cum', 'suck', 'shit', 'php', 'html', 'alert', 'script'
    ];

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function hashUserPassword(string $password): string
    {
        return $this->passwordHasher->hashPassword(new UserPasswordDTO(null), $password);
    }

    public function isUserPasswordValid(UserEntity $user, string $password): bool
    {
        return $this->passwordHasher->isPasswordValid(new UserPasswordDTO($user->getPassword()), $password);
    }

    /**
     * @inheritDoc
     */
    public function generateRandomString(int $length = 32): string
    {
        $bytes = random_bytes($length);
        return substr(strtr(base64_encode($bytes), '+/', '-_'), 0, $length);
    }

    /** @inheritDoc */
    public function generateRandomStringWithoutSymbols(int $length = 32): string
    {
        $symbolsForDelete = ['!','%', '-', '_', '&', '#', '@', '+', '*', '^', '$', '~'];
        $hash = '';
        while (true) {
            while (true) {
                $hash .= $this->generateRandomString($length);
                $hash = str_replace($symbolsForDelete, '', $hash);

                if (strlen($hash) > $length) {
                    $hash = mb_substr($hash, 0, $length);
                    break;
                }
            }

            $existsBreak = false;
            foreach (self::BREAK_WORDS as $val) {
                if (preg_match('/' .$val .'/', strtolower($hash))) {
                    $existsBreak = true;
                }
            }
            if ($existsBreak) {
                $hash = '';
            } else {
                break;
            }

        }
        return $hash;
    }
}
