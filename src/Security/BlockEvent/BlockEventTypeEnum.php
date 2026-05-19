<?php

declare(strict_types=1);

namespace App\Security\BlockEvent;

use Exception;

enum BlockEventTypeEnum: string
{
    case Validation = "validation";
    case DecodeBody = "decode_body";
    case SignIn = "sign_in";
    case Unauthorized = "unauthorized";
    case RefreshToken = "refresh_token";
    case NotFound = "not_found";
    case Other = "other";
    case BruteForce = "brute_force";

    /**
     * @throws Exception
     */
    public function getMaxCount(BlockEventParanoiaEnum $paranoiaEnum): int
    {
        if ($paranoiaEnum === BlockEventParanoiaEnum::Level_1) {
            return match ($this) {
                self::Validation, self::BruteForce => 60,
                self::DecodeBody, self::SignIn => 40,
                self::Unauthorized, self::NotFound => 50,
                self::RefreshToken => 70,
                self::Other => 300,
            };
        } else if ($paranoiaEnum === BlockEventParanoiaEnum::Level_2) {
            return match ($this) {
                self::Validation, self::BruteForce => 40,
                self::DecodeBody, self::SignIn => 25,
                self::Unauthorized, self::NotFound => 30,
                self::RefreshToken => 45,
                self::Other => 150,
            };
        } else if ($paranoiaEnum === BlockEventParanoiaEnum::Level_3) {
            return match ($this) {
                self::Validation, self::BruteForce, self::RefreshToken => 20,
                self::DecodeBody, self::SignIn => 15,
                self::Unauthorized, self::NotFound => 10,
                self::Other => 70,
            };
        }
        throw new Exception('paranoiaEnum not found');
    }
}
