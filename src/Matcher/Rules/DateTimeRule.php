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

class DateTimeRule extends AbstractRule
{
    /**
     * @var string
     */
    protected $format;

    public function __construct(string $format, $sample = null)
    {
        $this->assertSupport($format);

        parent::__construct($sample);

        $this->format = $format;

        if (null !== $sample) {
            $this->assertMatch($sample);
        }
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function assertMatch($test): void
    {
        $datetime = \DateTimeImmutable::createFromFormat($this->format, $test);

        if (!$datetime instanceof \DateTimeInterface) {
            throw new Mismatches\ValueMismatch('Cannot convert value {{ actual }} into a valid DateTime using {{ expected }} format', $this->format, $test);
        }
    }

    protected function assertSupport($value): void
    {
        if (!is_string($value)) {
            throw new Mismatches\TypeMismatch('string', gettype($value));
        }

        if ('' === $value) {
            throw new Mismatches\TypeMismatch('string', 'empty', 'Format cannot be an empty string');
        }
    }
}
