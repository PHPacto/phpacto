<?php

/*
 * This file is part of PHPacto
 *
 * Copyright (c) 2017  Damian Długosz <bigfootdd@gmail.com>
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

class VersionRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $rule = new VersionRule('dev');

        $expected = [
            '@rule' => VersionRule::class,
            'value' => 'dev',
            'operator' => '=',
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        return [
            [false, 5],
            [false, 1.0],
            [false, ''],
            [false, true],
            [false, false],
            [false, null],
            [false, new class() {
            }],
            [false, new \stdClass()],
            [false, []],
            [true, 'dev'],
            [true, '1'],
            [true, '1.2'],
            [true, '1.2.3'],
            [true, '1.2.3-dev'],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     *
     * @param mixed $value
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(VersionRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            $this->expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(VersionRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 'dev', '=', 'dev'],
            [true, '1', '<', 'dev'],
            [true, '1', '>', '1.2'],
            [true, '1.2', '>', '1.2.3'],
            [true, '1.2.3', '<', '1.2.3-dev'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 'dev', '=', '0'],
            [false, '1', '>=', 'dev'],
            [false, '1', '<=', '1.2'],
            [false, '1.2', '<=', '1.2.3'],
            [false, '1.2.3', '>=', '1.2.3-dev'],
        ];
    }

    /**
     * @dataProvider matchesTrueProvider
     * @dataProvider matchesFalseProvider
     *
     * @param mixed $ruleValue
     * @param mixed $operator
     * @param mixed $testValue
     */
    public function testMatch(bool $shouldMatch, $ruleValue, $operator, $testValue)
    {
        if (!$shouldMatch) {
            $this->expectException(Mismatches\ValueMismatch::class);
        }

        new VersionRule($ruleValue, $operator, $testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
