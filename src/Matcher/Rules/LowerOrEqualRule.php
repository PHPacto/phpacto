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

class LowerOrEqualRule extends AbstractRule
{
    /**
     * @param $value
     * @param $sample
     */
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
        if (is_string($this->value) && !is_string($test)) {
            throw new Mismatches\TypeMismatch(gettype($this->value), gettype($test), 'Cannot compare different data types. A {{ expected }} was expected, but got {{ actual }} instead');
        }

        if (!($test <= $this->value)) {
            throw new Mismatches\ValueMismatch('Value {{ actual }} should be lower than or equal to {{ expected }}', $this->value, $test);
        }
    }

    protected function assertSupport($value): void
    {
        if (!(is_numeric($value) || is_string($value))) {
            throw new Mismatches\TypeMismatch(['number', 'string'], gettype($value), 'Only {{ expected }} types are supported, {{ actual }} was provided');
        }
    }
}
