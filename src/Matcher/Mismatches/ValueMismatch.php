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

namespace Bigfoot\PHPacto\Matcher\Mismatches;

class ValueMismatch extends Mismatch
{
    private $actual;
    private $expected;

    /**
     * @param string $message
     * @param mixed  $expected
     * @param mixed  $actual
     */
    public function __construct(string $message, $expected, $actual)
    {
        $this->message = str_replace(
            ['{{ expected }}', '{{ actual }}'],
            [self::strJoin((array) $expected), self::wrap($actual)],
            $message
        );
        $this->expected = $expected;
        $this->actual = $actual;
    }

    /**
     * @return mixed
     */
    public function getActual()
    {
        return $this->actual;
    }

    /**
     * @return mixed
     */
    public function getExpected()
    {
        return $this->expected;
    }

    /**
     * @param array  $values
     * @param string $glue
     *
     * @return string
     */
    protected static function strJoin(array $values, string $glue = ' or '): string
    {
        $callback = function ($value) {
            return self::wrap((string) $value);
        };

        return implode($glue, array_map($callback, $values));
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected static function wrap($value): string
    {
        if (null === $value) {
            return 'NULL';
        }
        if (false === $value) {
            return 'FALSE';
        }
        if (true === $value) {
            return 'TRUE';
        }
        if (is_float($value)) {
            return sprintf('%G', $value);
        }
        if (is_int($value)) {
            return (string) $value;
        }
        switch ($value) {
            case 'array':
            case 'double':
            case 'boolean':
            case 'float':
            case 'number':
            case 'object':
            case 'integer':
            case 'string':
                return sprintf('`%s`', $value);
        }

        return sprintf('"%s"', (string) $value);
    }
}
