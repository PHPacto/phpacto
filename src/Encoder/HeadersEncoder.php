<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2018  Damian Długosz
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

abstract class HeadersEncoder
{
    private const EXCLUDED = [
        'host',
        'date',
        'accept-encoding',
        'connection',
        'content-length',
        'transfer-encoding',
    ];

    public static function encode(array $headers): array
    {
        $encoded = [];

        foreach ($headers as $name => $value) {
            if (is_array($value)) {
                $encoded[$name] = implode('; ', $value);
            } else {
                $encoded[$name] = $value;
            }
        }

        return $encoded;
    }

    public static function decode(array $headers): array
    {
        $decoded = [];

        foreach ($headers as $name => $value) {
            if (in_array(strtolower($name), self::EXCLUDED, true)) {
                continue;
            }
            if (is_array($value)) {
                $value = implode(';', $value);
            }
            $decoded[self::normalizeName($name)] = array_map('trim', explode(';', (string) $value));
        }

        return $decoded;
    }

    public static function normalizeName(string $name): string
    {
        return ucwords(strtolower($name), "- \t\r\n\f\v");
    }
}
