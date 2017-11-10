<?php

/*
 * This file is part of PHPacto
 *
 * Copyright (c) 2017  Damian Długosz <bigfootdd@gmail.com>
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

namespace Bigfoot\PHPacto\Matcher\Mismatches;

use PHPUnit\Framework\TestCase;

class KeyNotFoundMismatchTest extends TestCase
{
    public function test_has_message_string()
    {
        $miss = new KeyNotFoundMismatch('key');

        self::assertEquals('Key `key` was not found', $miss->getMessage());
    }

    public function test_is_has_expected_key_name()
    {
        $miss = new KeyNotFoundMismatch('key');

        self::assertEquals('key', $miss->getExpected());
    }
}
