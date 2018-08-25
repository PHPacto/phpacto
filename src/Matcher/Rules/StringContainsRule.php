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

class StringContainsRule extends StringRule
{
    /**
     * @var string
     */
    protected $value;

    public function __construct(string $value, string $sample = null, bool $caseSensitive = false)
    {
        $this->assertSupport($this->value = $caseSensitive ? $value : strtolower($value));

        parent::__construct($sample, $caseSensitive);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function assertMatch($test): void
    {
        parent::assertMatch($test);

        if ('' === $test) {
            throw new Mismatches\TypeMismatch('string', 'empty', 'Cannot search for an ampty string');
        }

        $function = $this->caseSensitive ? 'strpos' : 'stripos';

        if (false === $function($test, $this->value)) {
            throw new Mismatches\ValueMismatch('String {{ actual }} should contain {{ expected }}', $this->value, $test);
        }
    }

    protected function assertSupport(string $value): void
    {
        if ('' === $value) {
            throw new Mismatches\TypeMismatch('string', 'empty', 'Cannot compare empty strings');
        }
    }
}
