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

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;
use Bigfoot\PHPacto\Serializer\SerializerAwareTestCase;

class StringBeginsRuleTest extends SerializerAwareTestCase
{
    public function test_it_is_normalizable()
    {
        $rule = new StringBeginsRule('sam', 'sample');

        $expected = [
            '@rule' => 'stringBegins',
            'value' => 'sam',
            'sample' => 'sample',
            'case_sensitive' => true,
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 'a', 'a', true],
            [true, 'a', 'Abc', false],
            [true, 'A', 'aBC', false],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 'a', 'A', true],
            [false, 'a', 'b', true],
            [false, 'a', 'b', false],
            [false, 'a', '', false],
            [false, 'a', '', true],
            [false, 'B', 'abc', true],
            [false, 'd', 'abc', false],
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
        $rule = new StringBeginsRule($ruleValue, null, $caseSensitive);

        if (!$shouldMatch) {
            $this->expectException(Mismatches\ValueMismatch::class);
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
