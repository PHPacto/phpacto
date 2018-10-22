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
use Bigfoot\PHPacto\Serializer\SerializerAwareTestCase;

class StringEqualsRuleTest extends SerializerAwareTestCase
{
    public function test_it_is_normalizable_case_sensitive()
    {
        $rule = new StringEqualsRule('S', true);

        $data = $this->normalizer->normalize($rule);

        self::assertSame('S', $data);
    }

    public function test_it_is_normalizable_case_insensitive()
    {
        $rule = new StringEqualsRule('string', false);

        $expected = [
            '@rule' => 'stringEquals',
            'case_sensitive' => false,
            'value' => 'string',
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable_equals_case_sensitive()
    {
        $data = 'S';

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(StringEqualsRule::class, $rule);
        self::assertSame('S', $rule->getSample());
        self::assertSame('S', $rule->getValue());
        self::assertTrue($rule->isCaseSensitive());
    }

    public function test_it_is_denormalizable_case_insensitive()
    {
        $data = [
            '@rule' => 'stringEquals',
            'case_sensitive' => false,
            'value' => 'string',
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(StringEqualsRule::class, $rule);
        self::assertSame('string', $rule->getValue());
        self::assertFalse($rule->isCaseSensitive());
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
     *
     * @param mixed $ruleValue
     * @param mixed $testValue
     */
    public function testMatch(bool $shouldMatch, $ruleValue, $testValue, bool $caseSensitive)
    {
        $rule = new StringEqualsRule($ruleValue, $caseSensitive);

        if (!$shouldMatch) {
            $this->expectException(Mismatches\ValueMismatch::class);
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
