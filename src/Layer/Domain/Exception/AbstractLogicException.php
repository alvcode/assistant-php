<?php

declare(strict_types=1);

namespace App\Layer\Domain\Exception;

abstract class AbstractLogicException extends \Exception
{
    /** Unique error code */
    abstract public function getErrorKey(): string;
}
