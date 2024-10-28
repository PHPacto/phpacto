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

namespace PHPacto\Matcher\Rules;

use PHPacto\Matcher\Mismatches;
use PHPacto\Serializer\SerializerAwareTestCase;

class UrlRuleTest extends SerializerAwareTestCase
{
    public function test_it_has_a_default_sample()
    {
        $parameters = new ObjectRule(['p' => $this->rule->hasSample('param')]);
        $query = $this->rule->hasSample(['q' => 1, 'nest' => ['ed' => 2]], ObjectRule::class);
        $rule = new UrlRule('/path/{p}', $parameters, $query, 'https', 'hostname', 444);

        self::assertSame('https://hostname:444/path/param?q=1&nest%5Bed%5D=2', $rule->getSample());
    }

    public function test_it_is_normalizable()
    {
        $rule = new UrlRule('/path/location');

        $expected = [
            '_rule' => 'url',
            'location' => '/path/location',
            'case_sensitive' => true,
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_normalizable_full()
    {
        $childRule = $this->rule->empty();
        $parametersRule = new ObjectRule(['path' => $childRule]);
        $filterRule = new ObjectRule(['a' => $childRule]);
        $queryRule = new ObjectRule(['qp1' => $childRule, 'filters' => $filterRule]);
        $rule = new UrlRule('/{path}/location', $parametersRule, $queryRule, 'https', 'hostname', 443, 'https://hostname:443/path/location?qp1=A&filters[a]=1');

        $expected = [
            '_rule' => 'url',
            'scheme' => 'https',
            'hostname' => 'hostname',
            'port' => 443,
            'location' => '/{path}/location',
            'parameters' => [
                'path' => ['_rule' => \get_class($childRule)],
            ],
            'query' => [
                'qp1' => ['_rule' => \get_class($childRule)],
                'filters' => [
                    'a' => ['_rule' => \get_class($childRule)],
                ],
            ],
            'case_sensitive' => true,
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $data = [
            '_rule' => 'url',
            'location' => '/path/location',
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(UrlRule::class, $rule);
        self::assertSame(null, $rule->getScheme());
        self::assertSame(null, $rule->getHostname());
        self::assertSame(null, $rule->getPort());
        self::assertSame('/path/location', $rule->getLocation());
        self::assertSame(null, $rule->getParameters());
        self::assertSame(null, $rule->getQuery());
    }

    public function test_it_is_denormalizable_full()
    {
        $childRule = $this->rule->empty();

        $data = [
            '_rule' => 'url',
            'scheme' => 'https',
            'hostname' => 'hostname',
            'port' => 443,
            'location' => '/{path}/location',
            'parameters' => [
                'path' => ['_rule' => \get_class($childRule)],
            ],
            'query' => [
                'qp1' => ['_rule' => \get_class($childRule)],
            ],
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(UrlRule::class, $rule);
        self::assertSame('https', $rule->getScheme());
        self::assertSame('hostname', $rule->getHostname());
        self::assertSame(443, $rule->getPort());
        self::assertSame('/{path}/location', $rule->getLocation());
        self::assertInstanceOf(Rule::class, $rule->getParameters()->getProperties()['path']);
        self::assertInstanceOf(Rule::class, $rule->getQuery()->getProperties()['qp1']);
    }

    public function matchesProvider()
    {
        return [
            'empty' => [false, '', null, null, null, null, null, ''],
            'root path' => [true, '/', null, null, null, null, null, '/'],
            'relative path' => [false, 'relative/path', null, null, null, null, null, 'relative/path'],
            'absolute path' => [true, '/absolute/path', null, null, null, null, null, '/absolute/path'],
            'hostname query string' => [true, '/', null, null, null, null, null, '//hostname?query'],
            'hostname absolute query string' => [true, '/', null, null, null, null, null, '//hostname/?query=b'],
            'hostname and port' => [true, '/', null, null, null, null, null, '//hostname:80/'],
            'scheme and hostname' => [true, '/', null, null, null, null, null, 'http://hostname/'],
            'scheme and hostname and port' => [true, '/', null, null, null, null, null, 'https://hostname:433/'],
            'path parameters' => [true, '/{a}/{b}', new ObjectRule(['a' => $this->rule->empty(), 'b' => $this->rule->empty()]), null, null, null, null, '/1/2'],
            'path parameters not matching' => [false, '/{a}/{b}', new ObjectRule(['a' => $this->rule->empty(), 'b' => $this->rule->notMatching()]), null, null, null, null, '/1/2'],
            'path parameters missing keys' => [false, '/{missing}', new ObjectRule(['missing' => $this->rule->empty()]), null, null, null, null, '/'],
            'path parameters missing' => [false, '/{name}', null, null, null, null, null, '/'],
            'path without parameters not matching' => [false, '/test', null, null, null, null, null, '/other'],
            'query parameters' => [true, '/', null, new ObjectRule(['query' => $this->rule->empty()]), null, null, null, '/?query=1'],
            'query parameters not matching' => [false, '/', null, new ObjectRule(['query' => $this->rule->hasSampleNotMatching(1)]), null, null, null, '/?query=1'],
            'query parameters missing keys' => [false, '/', null, new ObjectRule(['query' => $this->rule->hasSample(1)]), null, null, null, '/'],
        ];
    }

    /**
     * @dataProvider matchesProvider
     */
    public function testMatch(bool $shouldMatch, string $location, ?ObjectRule $parameters, ?ObjectRule $query, ?string $scheme, ?string $hostname, ?int $port, string $testValue)
    {
        if (!$shouldMatch) {
            $this->expectException(Mismatches\Mismatch::class);
        }

        $rule = new UrlRule($location, $parameters, $query, $scheme, $hostname, $port);

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
