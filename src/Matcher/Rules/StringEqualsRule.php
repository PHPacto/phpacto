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

class StringEqualsRule extends StringComparisonRule
{
    public function assertMatch($test): void
    {
        parent::assertMatch($test);

        if ($this->caseSensitive) {
            if ($this->sample !== $test) {
                throw new Mismatches\ValueMismatch('String {{ actual }} should be equal to {{ expected }}', $this->sample, $test);
            }
        } else {
            if ($this->sample !== strtolower($test)) {
                throw new Mismatches\ValueMismatch('String {{ actual }} should be equal to {{ expected }}', $this->sample, $test);
            }
        }
    }
}
