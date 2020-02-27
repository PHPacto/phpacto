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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bigfoot\PHPacto\Encoder;

use PHPUnit\Framework\TestCase;

class HeadersEncoderTest extends TestCase
{
    public function test_decode_excludes_non_relevant_keys()
    {
        $headers = [
            'host' => 0,
            'date' => 0,
            'accept-encoding' => 0,
            'connection' => 0,
            'content-length' => 0,
            'transfer-encoding' => 0,
            'x' => 0, // <= this key should not been excluded
        ];

        $decoded = HeadersEncoder::decode($headers);

        self::assertCount(1, $decoded);
        self::assertArrayHasKey('X', $decoded);
    }

    public function test_decode_normalize_header_names()
    {
        $headers = [
            'COOKIE' => 0,
            'content-type' => 0,
            'x-custom-key' => 0,
        ];

        $decoded = HeadersEncoder::decode($headers);

        self::assertCount(3, $decoded);
        self::assertArrayHasKey('Cookie', $decoded);
        self::assertArrayHasKey('Content-Type', $decoded);
        self::assertArrayHasKey('X-Custom-Key', $decoded);
    }

    public function test_decode_headers_with_multiple_values_will_be_exploded()
    {
        $headers = [
            'x' => 'a; b=c, d, e=f',
        ];

        $decoded = HeadersEncoder::decode($headers);

        self::assertEquals([['a', 'b=c'], 'd', 'e=f'], $decoded['X']);
    }

    public function test_encode()
    {
        $headers = [
            'x' => 'val',
            'y' => [['a', 'b=c'], 'd', 'e=f'],
        ];

        $encoded = HeadersEncoder::encode($headers);

        self::assertCount(2, $encoded);
        self::assertEquals('val', $encoded['x']);
        self::assertEquals('a; b=c, d, e=f', $encoded['y']);
    }

    public function test_encode_content_type()
    {
        $headers = [
            'Content-Type' => [
                'application/json',
                'charset=UTF-8',
            ],
        ];

        $encoded = HeadersEncoder::encode($headers);

        self::assertEquals('application/json; charset=UTF-8', $encoded['Content-Type']);
    }
}
