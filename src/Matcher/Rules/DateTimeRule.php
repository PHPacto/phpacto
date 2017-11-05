<?php

/*
 * This file is part of PHPacto
 * Copyright (C) 2017  Damian Długosz
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
        if (!$test instanceof \DateTimeInterface
            && !\DateTimeImmutable::createFromFormat($this->value, $test) instanceof \DateTimeInterface
        ) {
            throw new Mismatches\ValueMismatch('Cannot convert value {{ actual }} into a valid DateTime using {{ expected }} format', $this->value, $test);
        }
    }

    public function getSample()
    {
        return \DateTimeImmutable::createFromFormat($this->value, $this->sample);
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
}
