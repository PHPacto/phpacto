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

class StringEqualsRuleTest extends SerializerAwareTestCase
{
    public function test_it_is_normalizable()
    {
        $rule = new StringEqualsRule('S');

        $data = $this->normalizer->normalize($rule);

        $expected = [
            '_rule' => 'stringEquals',
            'case_sensitive' => true,
            'sample' => 'S',
        ];

        self::assertEquals($expected, $data);
    }

    public function test_it_is_denormalizable_equals()
    {
        $data = [
            '_rule' => 'stringEquals',
            'case_sensitive' => true,
            'sample' => 'S',
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(StringEqualsRule::class, $rule);
        self::assertSame('S', $rule->getSample());
        self::assertTrue($rule->isCaseSensitive());
    }

    public function matchesTrueProvider()
    {
        return [
            [true, '', '', true],
            [true, '', '', false],
            [true, 'a', 'a', true],
            [true, 'a', 'A', false],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 'a', 'A', true],
            [false, 'a', 'b', true],
            [false, 'a', 'b', false],
            [false, 'a', '', true],
            [false, '', 'b', true],
        ];
    }

    /**
     * @dataProvider matchesTrueProvider
     * @dataProvider matchesFalseProvider
     */
    public function testMatch(bool $shouldMatch, string $ruleValue, string $testValue, bool $caseSensitive)
    {
        $rule = new StringEqualsRule($ruleValue, $caseSensitive);

        if (!$shouldMatch) {
            $this->expectException(Mismatches\ValueMismatch::class);
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
