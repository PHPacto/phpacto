<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2019  Damian Długosz
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

class RegexpRuleTest extends SerializerAwareTestCase
{
    public function test_it_is_normalizable()
    {
        $rule = new RegexpRule('^$', true, false, '');

        $expected = [
            '@rule' => 'regex',
            'pattern' => '^$',
            'sample' => '',
            'case_sensitive' => true,
            'multi_line' => false,
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $data = [
            '@rule' => 'regex',
            'pattern' => '^$',
            'sample' => '',
            'case_sensitive' => false,
            'multi_line' => true,
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(RegexpRule::class, $rule);
        self::assertSame('^$', $rule->getPattern());
        self::assertSame('', $rule->getSample());
        self::assertFalse($rule->isCaseSensitive());
        self::assertTrue($rule->isMultiLine());
    }

    public function supportedValuesProvider()
    {
        return [
            [false, 5],
            [false, 0.1],
            [true, 'string'],
            [true, '^(some|pattern)$'],
            [false, ')'],
            [false, '['],
            [false, true],
            [false, false],
            [false, null],
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
        if (!$shouldBeSupported) {
            $this->expectException(\Throwable::class);
        }

        new RegexpRule($value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testSampleIsMatchingRule()
    {
        $rule = self::getMockBuilder(RegexpRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects(self::once())
            ->method('assertMatch')
            ->with('content');

        $rule->__construct('pattern', true, false, 'content');
    }

    public function testExceptionIsTrhownIfSampleIsNotMatching()
    {
        $rule = new RegexpRule('.');

        $this->expectException(Mismatches\ValueMismatch::class);

        $rule->assertMatch('');
    }

    public function matchesTrueProvider()
    {
        return [
            [true, '^$', ''],
            [true, '^some (thing|else)$', 'some else'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, '0-9', 'F'],
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
        $rule = new RegexpRule($ruleValue);

        if (!$shouldMatch) {
            $this->expectException(Mismatches\ValueMismatch::class);
            $this->expectExceptionMessage('not matching the regex expression');
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
