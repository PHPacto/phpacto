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

declare(strict_types=1);

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class NotEqualsRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $rule = new NotEqualsRule(6);

        $expected = [
            '@rule' => NotEqualsRule::class,
            'value' => 6,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        return [
            [true, 100],
            [true, 1.0],
            [true, 'string'],
            [true, true],
            [true, false],
            [true, null],
            [true, []],
            [true, [[1]]],
            [false, new class() {}],
            [false, new \stdClass()],
            [false, [[new \stdClass()]]],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     *
     * @param mixed $value
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(NotEqualsRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            $this->expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(NotEqualsRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testSampleIsMatchingRule()
    {
        $rule = self::getMockBuilder(NotEqualsRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects(self::once())
            ->method('assertMatch')
            ->with(6)
            ->willReturn(true);

        $rule->__construct(5, 6);
    }

    /**
     * @depends testSampleIsMatchingRule
     */
    public function testExceptionIsTrhownIfSampleIsNotMatching()
    {
        $this->expectException(Mismatches\ValueMismatch::class);
        $this->expectExceptionMessage('should be different');

        new NotEqualsRule(5, 5);
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 5, 5],
            [false, 1, 1.0],
            [false, 'a', 'a'],
            [false, '', ''],
            [false, true, true],
            [false, false, false],
            [false, null, null],
        ];
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 5, '5'],
            [true, '', 0],
            [true, 1, true],
            [true, 0, false],
            [true, null, -1],
        ];
    }

    /**
     * @depends testSampleIsMatchingRule
     * @dataProvider matchesTrueProvider
     * @dataProvider matchesFalseProvider
     *
     * @param mixed $ruleValue
     * @param mixed $testValue
     */
    public function testMatch(bool $shouldMatch, $ruleValue, $testValue)
    {
        $this->markTestIncomplete('check later');

        if (!$shouldMatch) {
            $this->expectException(Mismatches\ValueMismatch::class);
        }

        new NotEqualsRule($ruleValue, $testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
