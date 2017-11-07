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

abstract class AbstractStringRule extends AbstractRule
{
    /**
     * @var bool
     */
    protected $caseSensitive;

    public function __construct($value, $sample = null, bool $caseSensitive = false)
    {
        $this->assertSupport($value);

        parent::__construct($value, $sample);

        $this->caseSensitive = $caseSensitive;

        if (null !== $sample) {
            $this->assertMatch($sample);
        }
    }

    public function assertMatch($test): void
    {
        self::assertSupport($test);
    }

    public function isCaseSensitive(): bool
    {
        return $this->caseSensitive;
    }

    protected function assertSupport($value): void
    {
        if (!is_string($value)) {
            throw new Mismatches\TypeMismatch('string', gettype($value));
        }
    }
}
