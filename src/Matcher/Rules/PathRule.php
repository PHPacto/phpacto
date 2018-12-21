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

class PathRule extends StringComparisonRule
{
    /**
     * @var string
     */
    protected $location;

    /**
     * @var Rule[]
     */
    protected $parameters;

    /**
     * @var Rule[]
     */
    protected $query;

    public function __construct(string $location, array $parameters = [], array $query = [], bool $caseSensitive = true, string $sample = null)
    {
        $this->assertSupport($location, $parameters, $query);

        $this->caseSensitive = $caseSensitive;
        $this->location = !$caseSensitive ? \strtolower($location) : $location;
        $this->parameters = $parameters;
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    public function assertMatch($test): void
    {
        parent::assertMatch($test);

        $this->assertSupport($test);

        preg_match('/^([^\?]*)\??(.*)$/', $test, $matches);

        [$null, $location, $query] = $matches;
        var_dump($test, [$location, $query]);

        $mismatches = [];

        // TODO: Move to assertMatchLocation()
        if ($this->location != $location) {
            $mismatches['LOCATION'] = new Mismatches\ValueMismatch('Absolute path should begin with "/"', 'ABSOLUTE PATH', $value);
        }

        // TODO: Move to assertMatchQuery()
        if ($this->query) {
            if ($query) {
                parse_str($query, $parsed);

                if ($parsed) $query = $parsed;
            }

            $mismatches['QUERY'] = new Mismatches\ValueMismatch('Absolute path should begin with "/"', 'ABSOLUTE PATH', $value);
        }

//        $path = $matches['path'] ?? '/';
//        $query = $matches['query'] ?? null;
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

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches, 'MISMATCH');
        }
    }

    public function assertMatchLocation($location, $parameters): void
    {
    }

    public function assertMatchQuery($query): void
    {
    }

    protected function assertSupport(string $value, array $parameters = [], array $query = []): void
    {
//        if (substr($value, 0, 1) !== '/') {
//            throw new Mismatches\ValueMismatch('Absolute path should begin with "/"', 'ABSOLUTE PATH', $value);
//        }
    }
}
