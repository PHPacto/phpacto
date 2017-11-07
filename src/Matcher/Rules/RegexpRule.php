<?php

/*
 * This file is part of PHPacto
 *
 * Copyright (c) 2017  Damian Długosz
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

class RegexpRule extends AbstractRule
{
    public function __construct($value, $sample = null)
    {
        $this->assertSupport($value);

        parent::__construct($value, $sample);

        if (null !== $sample) {
            $this->assertMatch($sample);
        }
    }

    public function assertMatch($test): void
    {
        if (!is_string($test)) {
            throw new Mismatches\TypeMismatch('string', gettype($test), 'Cannot match a Regex over a {{ actual }} type. A {{ expected }} is expected');
        }

        if (!preg_match('/'.$this->value.'/', $test)) {
            throw new Mismatches\ValueMismatch('Value {{ actual }} is not matching the regex expression {{ expected }}', $this->value, $test);
        }
    }

    protected function assertSupport($value): void
    {
        if (!is_string($value)) {
            throw new Mismatches\TypeMismatch('string', gettype($value));
        } elseif (false === @preg_match('/'.$value.'/', null)) {
            throw new Mismatches\TypeMismatch('regex pattern', $value, 'Your expression is not valid, check syntax for your pattern {{ actual }}');
        }
    }
}
