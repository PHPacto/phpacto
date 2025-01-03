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

namespace PHPacto\Matcher\Mismatches;

use PHPUnit\Framework\TestCase;

class ValueMismatchTest extends TestCase
{
    public function test_has_message_string()
    {
        $miss = new ValueMismatch('Was expected {{ expected }}, instead got {{ actual }}', 'expected', 'actual');

        self::assertEquals('Was expected "expected", instead got "actual"', $miss->getMessage());
    }

    public function test_is_has_actual_and_expected_values()
    {
        $miss = new ValueMismatch('message', 'expected', 'actual');

        self::assertEquals('expected', $miss->getExpected());
        self::assertEquals('actual', $miss->getActual());
    }
}
