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
use PHPacto\Serializer\SerializerAwareTestCase;

class StringLengthRuleTest extends SerializerAwareTestCase
{
    public function test_it_is_normalizable()
    {
        $childRule = $this->rule->empty(ComparisonRule::class);
        $rule = new StringLengthRule($childRule, 'sample');

        $expected = [
            '_rule' => 'stringLength',
            'length' => ['_rule' => \get_class($childRule)],
            'sample' => 'sample',
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function matchesTrueProvider()
    {
        return [
            [true, new EqualsRule(0), ''],
            [true, new EqualsRule(6), 'string'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, new EqualsRule(1), ''],
            [false, new EqualsRule(0), ' '],
        ];
    }

    /**
     * @dataProvider matchesTrueProvider
     * @dataProvider matchesFalseProvider
     *
     * @param mixed $ruleValue
     * @param mixed $testValue
     */
    public function testMatch(bool $shouldMatch, $ruleValue, $testValue)
    {
        $rule = new StringLengthRule($ruleValue);

        if (!$shouldMatch) {
            $this->expectException(Mismatches\ValueMismatch::class);
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
