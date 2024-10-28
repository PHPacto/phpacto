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

class UuidRuleTest extends SerializerAwareTestCase
{
    public function test_it_has_a_default_sample()
    {
        $rule = new UuidRule();

        self::assertSame('00000000-0000-0000-0000-000000000000', $rule->getSample());
    }

    public function test_it_is_normalizable()
    {
        $rule = new UuidRule();

        $expected = [
            '_rule' => 'uuid',
            'case_sensitive' => false,
            'sample' => '00000000-0000-0000-0000-000000000000'
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_normalizable_full()
    {
        $rule = new UuidRule('a3911bca-30f5-452f-8d40-b4d8cbd81faa');

        $expected = [
            '_rule' => 'uuid',
            'case_sensitive' => false,
            'sample' => 'a3911bca-30f5-452f-8d40-b4d8cbd81faa',
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function testSampleIsMatchingRule()
    {
        $rule = self::getMockBuilder(UuidRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects(self::once())
            ->method('assertMatch')
            ->with('uuid');

        $rule->__construct('uuid');
    }

    /**
     * @depends testSampleIsMatchingRule
     */
    public function testExceptionIsTrhownIfSampleIsNotUUID()
    {
        $this->expectException(Mismatches\ValueMismatch::class);

        new UuidRule('.');
    }

    public function matchesTrueProvider()
    {
        return [
            'Empty' => [true, '00000000-0000-0000-0000-000000000000'],
            'v1' => [true, 'e4eaaaf2-d142-11e1-b3e4-080027620cdd'],
            'v3' => [true, '11a38b9a-b3da-360f-9353-a5a725514269'],
            'v4' => [true, '25769c6c-d34d-4bfe-ba98-e0ee856f3e7a'],
            'v5' => [true, 'c4a760a8-dbcf-5254-a0d9-6a4474bd1b62'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 0],
            [false, 0.1],
            [false, ''],
            [false, 'n0t-4n-uu1d'],
            [false, null],
            [false, false],
            [false, true],
        ];
    }

    /**
     * @dataProvider matchesTrueProvider
     * @dataProvider matchesFalseProvider
     *
     * @param mixed $testValue
     */
    public function testMatch(bool $shouldMatch, $testValue)
    {
        $rule = new UuidRule();

        if (!$shouldMatch) {
            $this->expectException(Mismatches\ValueMismatch::class);
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
