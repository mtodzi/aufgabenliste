<?php

namespace App\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AppPermission
{
    public function __construct(
        public string $name,
        public string $description = ''
    ) {
    }
}