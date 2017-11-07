<?php

/*
 * This file is part of PHPacto
 * Copyright (C) 2017  Damian Długosz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class StringEqualsRule extends AbstractStringRule
{
    public function __construct($value, bool $caseSensitive = false)
    {
        parent::__construct($value, $value, $caseSensitive);
    }

    public function assertMatch($test): void
    {
        parent::assertMatch($test);

        if ($this->caseSensitive) {
            if ($this->value !== $test) {
                throw new Mismatches\ValueMismatch('String {{ actual }} should be equal to {{ expected }}', $this->value, $test);
            }
        } else {
            if (strtolower($this->value) !== strtolower($test)) {
                throw new Mismatches\ValueMismatch('String {{ actual }} should be equal to {{ expected }}', $this->value, $test);
            }
        }
    }
}
