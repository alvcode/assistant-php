<?php

declare(strict_types=1);

namespace App\Request\LongPolling;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints;

final class PoolRequest extends BaseRequest
{
    #[Constraints\Type('digit')]
    #[Constraints\Range(max: self::INT_8_MAX)]
    public mixed $last_event_id = null;

    public function getLastEventId(): ?int
    {
        return !is_null($this->last_event_id) ? (int)$this->last_event_id : null;
    }
}
