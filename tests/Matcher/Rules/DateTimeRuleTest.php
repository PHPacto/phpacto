<?php

declare(strict_types=1);

/*
 * PHPacto - Contract testing solution
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
use Bigfoot\PHPacto\Serializer\SerializerAwareTestCase;

class DateTimeRuleTest extends SerializerAwareTestCase
{
    public function test_it_is_normalizable()
    {
        $rule = new DateTimeRule('*', '0');

        $expected = [
            '@rule' => 'datetime',
            'format' => '*',
            'sample' => '0',
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $data = [
            '@rule' => 'datetime',
            'format' => '*',
            'sample' => '0',
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(DateTimeRule::class, $rule);
        self::assertSame('*', $rule->getFormat());
        self::assertSame('0', $rule->getSample());
    }

    public function supportedValuesProvider()
    {
        return [
            [false, 5],
            [false, 1.0],
            [true, 'string'],
            [true, '2017-09-23'],
            [false, true],
            [false, false],
            [false, null],
            [false, new class() {
            }],
            [false, new \stdClass()],
            [false, []],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     *
     * @param mixed $value
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(DateTimeRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            $this->expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(DateTimeRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testSampleIsMatchingRule()
    {
        $rule = self::getMockBuilder(DateTimeRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects(self::once())
            ->method('assertMatch')
            ->with('23-03-1990')
            ->willReturn(true);

        $rule->__construct('d-m-Y', '23-03-1990');
    }

    public function testExceptionIsTrhownIfSampleIsNotMatching()
    {
        $rule = new DateTimeRule('d-m-Y');

        $this->expectException(Mismatches\ValueMismatch::class);

        $rule->assertMatch('1990');
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 'Y-m-d', '2017-10-31'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 'test', ''],
            [false, 'Y-m-d', '2017.10.31'],
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
        $rule = new DateTimeRule($ruleValue);

        if (!$shouldMatch) {
            $this->expectException(Mismatches\ValueMismatch::class);
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if thrown if matching');
    }
}
