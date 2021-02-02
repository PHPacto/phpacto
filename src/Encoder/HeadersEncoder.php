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

namespace PHPacto\Encoder;

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
            if (\is_array($value)) {
                $encoded[$name] = self::encodeLevel0($value);
            } else {
                $encoded[$name] = $value;
            }
        }

        return $encoded;
    }

    /**
     * @param array|string $values
     *
     * @return array|string
     */
    public static function encodeLevel0($values): string
    {
        $encoded = [];

        foreach ($values as $key => $value) {
            if (null === $value) {
                $encoded[$key] = $key;
                continue;
            }

            if (\is_array($value)) {
                $value = self::encodeLevel1($value);
            }

            if (is_int($key)) {
                $encoded[$key] = $value;
                continue;
            }

            $encoded[$key] = sprintf('%s="%s"', $key, $value);
        }

        return implode('; ', $encoded);
    }

    /**
     * @param array|string $values
     *
     * @return string
     */
    public static function encodeLevel1($values)
    {
        if (\is_array($values)) {
            return implode(', ', $values);
        }

        return $values;
    }

    public static function decode(array $headers): array
    {
        $decoded = [];

        foreach ($headers as $name => $value) {
            if (\in_array(strtolower($name), self::EXCLUDED, true)) {
                continue;
            }

            if (is_array($value) && 1 === count($value) && 0 === array_key_last($value)) {
                $value = $value[0];
            }

            $name = self::normalizeName($name);
            $value = self::decodeLevel0($value);
            $decoded[$name] = $value;
        }

        return $decoded;
    }

    /**
     * @param array|string $raw
     *
     * @return array|string
     */
    public static function decodeLevel0($raw)
    {
        $decoded = [];

        if (\is_string($raw)) {
            $raw = explode(';', $raw);
        }

        if (\is_array($raw)) {
            foreach ($raw as $value) {
                $decoded[] = self::decodeLevel1($value);
            }
        } else {
            $decoded[] = $raw;
        }

        if (1 === \count($decoded)) {
            return $decoded[0];
        }

        return $decoded;
    }

    /**
     * @param array|string $raw
     *
     * @return array|string
     */
    private static function decodeLevel1($raw)
    {
        $decoded = [];

        if (\is_string($raw)) {
            $raw = explode(',', $raw);
        }

        if (\is_array($raw)) {
            foreach ($raw as $value) {
                $decoded[] = trim($value);
            }
        } else {
            $decoded[] = $raw;
        }

        if (1 === \count($decoded)) {
            return $decoded[0];
        }

        return $decoded;
    }

    public static function normalizeName(string $name): string
    {
        return ucwords(strtolower($name), "- \t\r\n\f\v");
    }
}
