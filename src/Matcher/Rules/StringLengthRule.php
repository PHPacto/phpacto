<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2018  Damian Długosz
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
    /**
     * @var ComparisonRule
     */
    protected $length;

    public function __construct(ComparisonRule $length, $sample = null)
    {
        $this->length = $length;

        parent::__construct($sample);
    }

    public function getLength(): ComparisonRule
    {
        return $this->length;
    }

    public function assertMatch($test): void
    {
        if (!\is_string($test)) {
            throw new Mismatches\TypeMismatch('string', \gettype($test));
        }

        try {
            $this->length->assertMatch(\strlen($test));
        } catch (Mismatches\Mismatch $mismatch) {
            throw new Mismatches\ValueMismatch(
                'The length of string {{ actual }} should match the rule:' . "\n" .
                '    {{ expected }}',
                $mismatch->getMessage(),
                $test
            );
        }
    }
}
