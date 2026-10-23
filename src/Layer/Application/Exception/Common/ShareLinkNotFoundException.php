<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\Common;

use App\Layer\Domain\Exception\AbstractLogicException;

class ShareLinkNotFoundException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_share_link_not_found';
    }
}
