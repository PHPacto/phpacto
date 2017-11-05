<?php

/*
 * This file is part of PHPacto
 * Copyright (C) 2017  Damian Długosz
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

class ContainsRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $childRule = $this->rule->empty();
        $rule = new ContainsRule($childRule, [1, 2, 3]);

        $expected = [
            '@rule' => ContainsRule::class,
            'value' => ['@rule' => get_class($childRule), 'value' => null],
            'sample' => [1, 2, 3],
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        $rule = self::getRuleMockFactory()->empty();

        return [
            [false, 5],
            [false, 1.0],
            [false, 'string'],
            [false, true],
            [false, false],
            [false, null],
            [false, new class() {
            }],
            [false, new \stdClass()],
            [false, []],
            [true, $rule],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     *
     * @param mixed $value
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(ContainsRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            self::expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(ContainsRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function matchesTrueProvider()
    {
        $rule = new EqualsRule(3);

        return [
            [true, $rule, [1, 2, 3]],
        ];
    }

    public function matchesFalseProvider()
    {
        $rule = new EqualsRule(0);

        return [
            [false, $rule, []],
            [false, $rule, [5, 4, 5]],
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
        if (!$shouldMatch) {
            self::expectException(Mismatches\ValueMismatch::class);
            self::expectExceptionMessage('At least one item');
        }

        new ContainsRule($ruleValue, $testValue);

        self::assertTrue(true, 'No exceptions should be thrown');
    }
}
