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

namespace PHPacto\Encoder;

use PHPacto\Matcher\Mismatches\ValueMismatch;
use PHPUnit\Framework\TestCase;

class BodyEncoderTest extends TestCase
{
    public function test_it_returns_a_string_by_default()
    {
        $body = 'String';
        $expected = 'String';

        self::assertEquals($expected, BodyEncoder::decode($body));

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function test_it_throws_exception_when_invalid_form_data()
    {
        $this->expectException(ValueMismatch::class);

        BodyEncoder::decode('', 'application/x-www-form-urlencoded');
    }

    public function test_it_can_parse_form_data()
    {
        $body = 'a=1&b[]=2&b[]=3&c[d]=+';
        $expected = [
            'a' => '1',
            'b' => [2, 3],
            'c' => [
                'd' => ' ',
            ],
        ];

        self::assertEquals($expected, BodyEncoder::decode($body, 'application/x-www-form-urlencoded'));
    }

    public function test_it_can_parse_form_url_econded_data()
    {
        $body = 'a=1&b%5B0%5D=2&b%5B1%5D=3&c%5Bd%5D=+';
        $expected = [
            'a' => '1',
            'b' => [2, 3],
            'c' => [
                'd' => ' ',
            ],
        ];

        self::assertEquals($expected, BodyEncoder::decode($body, 'application/x-www-form-urlencoded'));
    }

    public function test_it_throws_exception_when_invalid_json_string()
    {
        $this->expectException(ValueMismatch::class);

        BodyEncoder::decode('', 'application/json');
    }

    public function test_it_can_parse_json_data()
    {
        $body = '{"a":1,"0":[2,true,null]}';
        $expected = [
            'a' => '1',
            '0' => [2, true, null],
        ];

        self::assertEquals($expected, BodyEncoder::decode($body, 'application/json'));
    }
}
