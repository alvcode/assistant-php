<?php

declare(strict_types=1);

namespace App\Security\BlockEvent;

use Exception;

enum BlockEventParanoiaEnum: int
{
    case Off = 0;
    case Level_1 = 1;
    case Level_2 = 2;
    case Level_3 = 3;

    /**
     * @throws Exception
     */
    public function blockingMinutes(): int
    {
        return match ($this) {
            self::Level_1, self::Off  => 40,
            self::Level_2 => 420, // 7 hours
            self::Level_3 => 2880, // 2 day
        };
    }
}
