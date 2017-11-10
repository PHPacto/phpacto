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

class RegexpRule extends StringRule
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var boolean
     */
    protected $multiLine;

    public function __construct(string $pattern, $sample = null, bool $caseSensitive = false, bool $multiLine = false)
    {
        $this->assertSupport($pattern);

        parent::__construct($sample);

        $this->pattern = $pattern;
        $this->multiLine = $multiLine;

        if (null !== $sample) {
            $this->assertMatch($sample);
        }
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function assertMatch($test): void
    {
        if (!is_string($test)) {
            throw new Mismatches\TypeMismatch('string', gettype($test), 'Cannot match a Regex over a {{ actual }} type. A {{ expected }} is expected');
        }

        $modifiers = '';

        if (!$this->caseSensitive) {
            $modifiers .= 'i';
        }

        if ($this->multiLine) {
            $modifiers .= 'm';
        }

        if (!preg_match('/'.$this->pattern.'/'.$modifiers, $test)) {
            throw new Mismatches\ValueMismatch('Value {{ actual }} is not matching the regex expression {{ expected }}', $this->pattern, $test);
        }
    }

    protected function assertSupport(string $value): void
    {
        if (false === @preg_match('/'.$value.'/', null)) {
            throw new Mismatches\TypeMismatch('regex pattern', $value, 'Your expression is not valid, check syntax for your pattern {{ actual }}');
        }
    }
}
