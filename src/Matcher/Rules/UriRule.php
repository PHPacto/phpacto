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

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class UriRule extends StringComparisonRule
{
    public function __construct(string $location, array $parameters = [], string $sample = null, bool $caseSensitive = true)
    {
//        $this->assertSupport($location);

        parent::__construct($location, $sample, $caseSensitive);
    }

    public function assertMatch($test): void
    {
        if (!\is_string($test)) {
            throw new Mismatches\TypeMismatch('string', \gettype($test));
        }

        $parsed = parse_url($test);

        if ($parsed === false) {
            throw new Mismatches\TypeMismatch('URI', $test, 'The string {{ actual }} is not a valid URI');
        }

        $parsed = [
            'host' => $parsed['host'] ?? null,
            'path' => $parsed['path'] ?? '',
            'query' => $parsed['query'] ?? null,
        ];

        var_dump($test, $parsed);

        if (strlen($parsed['path']) < 1 || $parsed['path'][0] != '/') {
            throw new Mismatches\TypeMismatch('URI', $test, 'The string {{ actual }} is not a valid URI');
        }

//        preg_match('/^(?:\/\/(?<host>[^\/\?]+))?(?<path>\/[^\?]*)(?:\?(?<query>.*))?$/', $test, $matches);
//
//        $path = $matches['path'] ?? '/';
//        $query = $matches['query'] ?? null;
//        $host = $matches['host'] ?? null;
//
//        var_dump([
//            'test' => $test,
//            'host' => $host,
//            'path' => $path,
//            'query' => $query,
//        ]);
//
//        if (count($matches) === 0) {
//            throw new Mismatches\TypeMismatch('URI', $test, 'The string {{ actual }} is not a valid URI');
//        }
    }

    protected function assertSupport(string $value): void
    {
//        if ('' === $value) {
//            throw new Mismatches\TypeMismatch('string', 'empty', 'Cannot compare empty strings');
//        }
    }
}
