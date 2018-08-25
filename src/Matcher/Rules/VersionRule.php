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

class VersionRule extends AbstractRule
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $operator;

    public function __construct(string $value, string $operator = '=', $sample = null)
    {
        $this->assertSupport($this->value = $value);

        $this->assertSupportOperator($this->operator = $operator);

        parent::__construct($sample);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    public function assertMatch($test): void
    {
        $this->assertSupport($test);

        if (!version_compare($test, $this->value, $this->operator)) {
            switch ($this->operator) {
                case '<': $operatorString = 'lower than'; break;
                case '<=': $operatorString = 'lower than or equal to'; break;
                case '=': $operatorString = 'equal to'; break;
                case '>=': $operatorString = 'greater than or equal to'; break;
                case '>': $operatorString = 'greater than'; break;
                default: $operatorString = '';
            }

            throw new Mismatches\ValueMismatch('Version {{ actual }} should be '.$operatorString.' {{ expected }}', $this->value, $test);
        }
    }

    protected function assertSupport($value): void
    {
        if (!is_string($value)) {
            throw new Mismatches\TypeMismatch('string', gettype($value));
        }

        if ('' === $value) {
            throw new Mismatches\TypeMismatch('string', 'empty');
        }
    }

    protected function assertSupportOperator($operator): void
    {
        $allowedOperators = ['<', '<=', '=', '>=', '>'];

        if (!in_array($operator, $allowedOperators, true)) {
            throw new Mismatches\ValueMismatch('Only one operator of {{ expected }} is supported, but given {{ actual }}', $allowedOperators, $operator);
        }
    }
}
