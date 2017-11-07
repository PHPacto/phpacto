<?php

/*
 * This file is part of PHPacto
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

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class EqualsRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $rule = new EqualsRule(5);

        $expected = 5;

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_normalizable_recursive()
    {
        $childRule = $this->rule->empty();
        $rule = new EqualsRule([
            $childRule,
            'key' => $childRule,
            'nested' => [
                'key' => $childRule,
            ],
        ]);

        $expected = [
            ['@rule' => get_class($childRule), 'value' => null],
            'key' => ['@rule' => get_class($childRule), 'value' => null],
            'nested' => [
                'key' => ['@rule' => get_class($childRule), 'value' => null],
            ],
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        $rule = self::getRuleMockFactory()->empty();

        return [
            [true, 100],
            [true, 1.0],
            [true, 'string'],
            [true, true],
            [true, false],
            [true, null],
            [true, []],
            [true, [[1]]],
            [true, [$rule]],
            [false, new class() {
            }],
            [false, new \stdClass()],
            [false, [new \stdClass()]],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     *
     * @param mixed $value
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(EqualsRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            self::expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(EqualsRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 5, 5],
            [true, 1, 1.0],
            [true, 'a', 'a'],
            [true, '', ''],
            [true, true, true],
            [true, false, false],
            [true, null, null],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 5, '5'],
            [false, '', 0],
            [false, 0, []],
            [false, 1, true],
            [false, 0, false],
            [false, false, null],
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
        $rule = new EqualsRule($ruleValue);

        if (!$shouldMatch) {
            self::expectException(Mismatches\ValueMismatch::class);
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
