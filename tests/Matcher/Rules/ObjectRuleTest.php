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

class ObjectRuleTest extends SerializerAwareTestCase
{
    public function test_it_is_normalizable()
    {
        $childRule = $this->rule->empty();
        $rule = new ObjectRule(['prop' => $childRule], ['prop' => 'value']);

        $expected = [
            '@rule' => 'object',
            'properties' => [
                'prop' => ['@rule' => \get_class($childRule)],
            ],
            'sample' => [
                'prop' => 'value',
            ],
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $childRule = $this->rule->empty();

        $data = [
            '@rule' => 'object',
            'properties' => [
                'prop' => ['@rule' => \get_class($childRule)],
            ],
            'sample' => [
                'prop' => 'value',
            ],
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(ObjectRule::class, $rule);
        self::assertSame(['prop' => 'value'], $rule->getSample());
    }

    public function matchesTrueProvider()
    {
        $two = new EqualsRule(2);

        return [
            [true, ['two' => $two], ['one' => 1, 'two' => 2, 'three' => 3]],
        ];
    }

    public function matchesFalseProvider()
    {
        $zero = new EqualsRule(0);

        return [
            [false, ['zero' => $zero], []],
            [false, ['zero' => $zero], ['zero' => 1]],
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
        $rule = new ObjectRule($ruleValue);

        if (!$shouldMatch) {
            $this->expectException(Mismatches\MismatchCollection::class);
            $this->expectExceptionMessage('properties not matching');
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
