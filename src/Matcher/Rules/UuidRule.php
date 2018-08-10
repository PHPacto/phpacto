<?php

declare(strict_types=1);

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

class UuidRule extends AbstractRule
{
    private const PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    public function __construct($sample = '00000000-0000-0000-0000-000000000000')
    {
        parent::__construct($sample);
    }

    public function assertMatch($test): void
    {
        if (!is_string($test)) {
            throw new Mismatches\TypeMismatch('string', gettype($test));
        }

        if (!preg_match(self::PATTERN, $test)) {
            throw new Mismatches\ValueMismatch('Value {{ actual }} is not a valid UUID, expecting a string like {{ expected }}', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', $test);
        }
    }
}
