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

namespace PHPacto\Matcher\Rules;

use PHPacto\Matcher\Mismatches;
use Symfony\Component\Routing\Route;

class UrlRule extends StringRule
{
    /**
     * @var string|null
     */
    private $scheme;

    /**
     * @var string|null
     */
    private $hostname;

    /**
     * @var int|null
     */
    private $port;

    /**
     * @var string
     */
    private $location;

    /**
     * @var ObjectRule|null
     */
    private $parameters;

    /**
     * @var ObjectRule|null
     */
    private $query;

    public function __construct(string $location, ObjectRule $parameters = null, ObjectRule $query = null, string $scheme = null, string $hostname = null, int $port = null, string $sample = null)
    {
        $this->assertSupport($location, $parameters);
        $this->scheme = $scheme;
        $this->hostname = $hostname;
        $this->port = $port;
        $this->location = $location;
        $this->parameters = $parameters;
        $this->query = $query;

        parent::__construct($sample);
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getParameters(): ?ObjectRule
    {
        return $this->parameters;
    }

    public function getQuery(): ?ObjectRule
    {
        return $this->query;
    }

    public function getSample()
    {
        if (null === $this->sample) {
            $this->sample = $this->location;

            if ($this->parameters) {
                foreach ($this->parameters->getProperties() as $paramenterName => $parameterRule) {
                    $this->sample = str_replace('{'.$paramenterName.'}', $parameterRule->getSample(), $this->sample);
                }
            }

            if ($this->query) {
                $this->sample .= '?'. http_build_query($this->query->getSample());
            }

            if ($this->scheme || $this->hostname || $this->port) {
                $this->sample = sprintf('%s://%s:%s',
                    $this->scheme ?? 'http',
                    $this->hostname ?? 'localhost',
                    $this->port ?? ($this->scheme === 'https' ? 443 : 80)
                ) . $this->sample;
            }
        }

        return $this->sample;
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

        $parsed = array_merge([
            'scheme' => 'http',
            'host' => 'localhost',
            'port' => 'https' === ($parsed['scheme'] ?? 'http') ? 443 : 80,
            'path' => '/',
            'query' => null,
        ], $parsed);
        parse_str($parsed['query'], $parsed['query']);

        if ($parsed['path'][0] !== '/') {
            throw new Mismatches\TypeMismatch('URI', $test, 'The string {{ actual }} is not a valid URI');
        }

        $mismatches = [];

        if ($this->scheme && $parsed['scheme'] !== $this->scheme) {
            $mismatches['SCHEME'] = new Mismatches\ValueMismatch('Scheme should be {{ expected }} but is {{ actual }}', $this->scheme, $parsed['scheme']);
        }

        if ($this->hostname && $parsed['host'] !== $this->hostname) {
            $mismatches['HOSTNAME'] = new Mismatches\ValueMismatch('Hostname should be {{ expected }} but is {{ actual }}', $this->hostname, $parsed['host']);
        }

        if ($this->port && $parsed['port'] !== $this->port) {
            $mismatches['PORT'] = new Mismatches\ValueMismatch('Port should be {{ expected }} but is {{ actual }}', $this->port, $parsed['port']);
        }

        if ($this->parameters) {
            $route = new Route($this->location);
            preg_match($route->compile()->getRegex(), $parsed['path'], $matches);

            if (!empty($matches)) {
                try {
                    $this->parameters->assertMatch($matches);
                } catch (Mismatches\Mismatch $e) {
                    $mismatches['LOCATION'] = $e;
                }
            } else {
                $mismatches['LOCATION'] = new Mismatches\ValueMismatch('Some keys ware not found in your path {{ actual }} but expected is {{ expected }}', $this->location, $parsed['path']);
            }
        } elseif ($this->location !== $parsed['path']) {
            $mismatches['LOCATION'] = new Mismatches\ValueMismatch('Path location {{ actual }} does not match {{ expected }}', $this->location, $parsed['path']);
        }

        if ($this->query) {
            try {
                $this->query->assertMatch($parsed['query']);
            } catch (Mismatches\Mismatch $e) {
                $mismatches['QUERY'] = $e;
            }
        }

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches);
        }
    }

    protected function assertSupport($value, ObjectRule $parameters = null): array
    {
        parent::assertMatch($value);

        if ('' === $value) {
            throw new Mismatches\ValueMismatch('Path cannot be empty', 'valid path location', $value);
        }

        if ('/' !== $value[0]) {
            throw new Mismatches\ValueMismatch('Only absolute paths are allowed', 'an absolute path', $value);
        }

        // Root path without parameters
        if ('/' === $value && !$parameters) {
            return [];
        }

        preg_match_all('/(?:(?:[\w-]|(\{[^\}]+\})+)+\/?)/', $value, $matches);

        if (empty($matches)) {
            throw new Mismatches\ValueMismatch('Invalid path, check your location syntax', 'valid path location', $value);
        }

        $placeholders = array_filter($matches[1] ?? []);

        $map = static function ($input) {
            return substr($input, 1 , -1);
        };

        $names = array_map($map, $placeholders);
        sort($names);

        $paramNames = $parameters ? array_keys($parameters->getProperties()) : [];
        sort($paramNames);

        if ($paramNames != $names) {
            foreach (self::getMissingKeys($names, $paramNames) as $missing) {
                throw new Mismatches\KeyNotFoundMismatch($missing);
            }
        }

        return $placeholders;
    }

    private static function getMissingKeys(array $a1, array $a2): array
    {
        return array_diff($a1, $a2) + array_diff($a2, $a1);
    }
}
