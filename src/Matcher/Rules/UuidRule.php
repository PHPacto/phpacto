<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) Damian Długosz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 */

namespace PHPacto\Matcher\Rules;

use PHPacto\Matcher\Mismatches;

class UuidRule extends StringRule
{
    private const PATTERN = '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-6][0-9A-F]{3}-[089ab][0-9A-F]{3}-[0-9A-F]{12}$/';

    public function __construct(string $sample = null, bool $caseSensitive = false)
    {
        parent::__construct($sample ?? '00000000-0000-0000-0000-000000000000', $caseSensitive);
    }

    public function assertMatch($test): void
    {
        if (!\is_string($test)) {
            throw new Mismatches\TypeMismatch('string', \gettype($test));
        }

        if (!preg_match(self::PATTERN.($this->caseSensitive ? '' : 'i'), $test)) {
            throw new Mismatches\ValueMismatch('Value {{ actual }} is not a valid UUID, expecting a string like {{ expected }}', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', $test);
        }
    }
}
