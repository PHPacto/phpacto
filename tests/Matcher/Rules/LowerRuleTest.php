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

class LowerRuleTest extends SerializerAwareTestCase
{
    public function test_it_has_a_default_sample()
    {
        $rule = new LowerRule(5);

        self::assertEquals(4, $rule->getSample());
    }

    public function test_it_is_normalizable()
    {
        $rule = new LowerRule(5, 4);

        $expected = [
            '_rule' => 'lower',
            'value' => 5,
            'sample' => 4,
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $data = [
            '_rule' => 'lower',
            'value' => 5,
            'sample' => 4,
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(LowerRule::class, $rule);
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
            [false, new class () {
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
        $rule = self::getMockBuilder(LowerRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            $this->expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(LowerRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testSampleIsMatchingRule()
    {
        $rule = self::getMockBuilder(LowerRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects(self::once())
            ->method('assertMatch')
            ->with(4);

        $rule->__construct(5, 4);
    }

    public function testExceptionIsTrhownIfSampleIsNotMatching()
    {
        $rule = new LowerRule(5);

        $this->expectException(Mismatches\ValueMismatch::class);
        $this->expectExceptionMessage('should be lower than');

        $rule->assertMatch(5);
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 5, 4.9],
            [true, '90', '0'],
            [true, 'zzz', 'a'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 5.0, 5],
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
        $rule = new LowerRule($ruleValue);

        if (!$shouldMatch) {
            $this->expectException(Mismatches\ValueMismatch::class);
            $this->expectExceptionMessage('should be lower than');
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
