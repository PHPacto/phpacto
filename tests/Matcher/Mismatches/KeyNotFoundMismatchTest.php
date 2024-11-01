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

class KeyNotFoundMismatchTest extends TestCase
{
    public function test_has_message_string()
    {
        $miss = new KeyNotFoundMismatch('key');

        self::assertStringContainsString('not found', $miss->getMessage());
    }

    public function test_is_has_expected_key_name()
    {
        $miss = new KeyNotFoundMismatch('key');

        self::assertEquals('key', $miss->getKeyName());
    }
}
