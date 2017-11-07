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

class StringLengthRule extends AbstractRule
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
            throw new Mismatches\TypeMismatch('string', gettype($test));
        }

        try {
            $this->value->assertMatch(strlen($test));
        } catch (Mismatches\Mismatch $mismatch) {
            throw new Mismatches\ValueMismatch(
                'The lenght of string {{ actual }} should match the rule:'."\n".
                '    {{ expected }}',
                $mismatch->getMessage(),
                strlen($test)
            );
        }
    }

    protected function assertSupport($value): void
    {
        if (!$value instanceof Rule) {
            throw new Mismatches\TypeMismatch('Rule', gettype($value), '{{ actual }} must be an instance of {{ expected }}');
        }
    }
}
