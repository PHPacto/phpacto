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

class LowerOrEqualRuleTest extends SerializerAwareTestCase
{
    public function test_it_has_a_default_sample()
    {
        $rule = new LowerOrEqualRule(5);

        self::assertEquals(4, $rule->getSample());
    }

    public function test_it_is_normalizable()
    {
        $rule = new LowerOrEqualRule(5, 4);

        $expected = [
            '_rule' => 'lowerEqual',
            'value' => 5,
            'sample' => 4,
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $data = [
            '_rule' => 'lowerEqual',
            'value' => 5,
            'sample' => 4,
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(LowerOrEqualRule::class, $rule);
        self::assertSame(5, $rule->getValue());
        self::assertSame(4, $rule->getSample());
    }

    public function supportedValuesProvider()
    {
        return [
            [true, 100],
            [true, 10.0],
            [true, 'string'],
            [false, true],
            [false, false],
            [false, null],
            [false, []],
            [false, new class() {
            }],
            [false, new \stdClass()],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     *
     * @param mixed $value
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(LowerOrEqualRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            $this->expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(LowerOrEqualRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testSampleIsMatchingRule()
    {
        $rule = self::getMockBuilder(LowerOrEqualRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects(self::once())
            ->method('assertMatch')
            ->with(5)
            ->willReturn(true);

        $rule->__construct(5, 5);
    }

    public function testExceptionIsTrhownIfSampleIsNotMatching()
    {
        $rule = new LowerOrEqualRule(5);

        $this->expectException(Mismatches\ValueMismatch::class);
        $this->expectExceptionMessage('should be lower than or equal to');

        $rule->assertMatch(6);
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 5, 5.0],
            [true, '90', '0'],
            [true, 'zzz', 'a'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 5, 5.01],
            [false, '', 'A'],
            [false, 'zzz', 'zzzz'],
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
        $rule = new LowerOrEqualRule($ruleValue);

        if (!$shouldMatch) {
            $this->expectException(Mismatches\ValueMismatch::class);
            $this->expectExceptionMessage('should be lower than or equal to');
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
