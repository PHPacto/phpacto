<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2018  Damian Długosz
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

class ContainsItemRuleTest extends SerializerAwareTestCase
{
    public function test_it_is_normalizable()
    {
        $childRule = $this->rule->empty();
        $rule = new ContainsItemRule($childRule, ['value']);

        $expected = [
            '@rule' => 'contains',
            'rule' => ['@rule' => \get_class($childRule)],
            'sample' => [
                'value',
            ],
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $childRule = $this->rule->empty();

        $data = [
            '@rule' => 'contains',
            'rule' => ['@rule' => \get_class($childRule)],
            'sample' => [
                'value',
            ],
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(ContainsItemRule::class, $rule);
        self::assertSame(['value'], $rule->getSample());
    }

    public function matchesTrueProvider()
    {
        $rule = new EqualsRule(2);

        return [
            [true, $rule, [1, 2, 3]],
        ];
    }

    public function matchesFalseProvider()
    {
        $rule = new EqualsRule(0);

        return [
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
            $this->expectException(Mismatches\MismatchCollection::class);
            $this->expectExceptionMessage('At least one item');
        }

        new ContainsItemRule($ruleValue, $testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }

    public function testMismatchEmpty()
    {
        $this->expectException(Mismatches\ValueMismatch::class);
        $this->expectExceptionMessage('empty');

        $childRule = $this->rule->empty();

        $rule = new ContainsItemRule($childRule);
        $rule->assertMatch([]);
    }
}
