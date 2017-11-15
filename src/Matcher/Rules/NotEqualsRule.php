<?php

/*
 * PHPacto - Contract testing solution
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

class NotEqualsRule extends AbstractRule
{
    /**
     * @var mixed
     */
    protected $value;

    public function __construct($value, $sample = null)
    {
        $this->assertSupport($this->value = $value);

        parent::__construct($sample);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function assertMatch($test): void
    {
        $types = [
            gettype($this->value),
            gettype($test),
        ];

        if ($types !== ['integer', 'double'] && $types !== ['double', 'integer']) {
            if ($types[0] !== $types[1]) {
                throw new Mismatches\TypeMismatch($types[0], $types[1], 'Cannot compare different data types. A {{ expected }} was expected, but got {{ actual }} instead');
            }
        }

        if ($this->value === $test) {
            throw new Mismatches\ValueMismatch('Value {{ actual }} should be different than {{ expected }}', $this->value, $test);
        }
    }

    protected function assertSupport($value): void
    {
        if (is_object($value)) {
            throw new Mismatches\TypeMismatch(['null', 'boolean', 'number', 'string', 'array'], gettype($value), 'Objects are not supported');
        } elseif (is_array($value)) {
            array_walk($value, function ($value) {
                $this->assertSupport($value);
            });
        }
    }
}
