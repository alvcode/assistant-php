<?php

namespace App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class NeedAuth
{
    public function __construct(public bool $required = true) {}
}
