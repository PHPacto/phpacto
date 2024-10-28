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

class RegexpRule extends StringRule
{
    public function __construct(
        protected string $pattern,
        protected bool $multiLine = false,
        $sample = null,
        bool $caseSensitive = true
    ) {
        $this->assertSupport($pattern);

        parent::__construct($sample, $caseSensitive);
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function isMultiLine(): bool
    {
        return $this->multiLine;
    }

    public function assertMatch($test): void
    {
        if (!\is_string($test)) {
            throw new Mismatches\TypeMismatch('string', \gettype($test), 'Cannot match a Regex over a {{ actual }} type. A {{ expected }} is expected');
        }

        $modifiers = '';

        if (!$this->caseSensitive) {
            $modifiers .= 'i';
        }

        if ($this->multiLine) {
            $modifiers .= 'm';
        }

        $pattern = '/' . $this->pattern . '/' . $modifiers;

        if (!preg_match($pattern, $test)) {
            throw new Mismatches\ValueMismatch('Value {{ actual }} is not matching the regex expression {{ expected }}', $pattern, $test);
        }
    }

    protected function assertSupport($value): void
    {
        parent::assertMatch($value);

        if (false === @preg_match('/' . $value . '/', '')) {
            throw new Mismatches\TypeMismatch('regex pattern', $value, 'Your expression is not valid, check syntax for your pattern {{ actual }}');
        }
    }
}
