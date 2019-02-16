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

class OrRuleTest extends SerializerAwareTestCase
{
    public function test_it_is_normalizable()
    {
        $childRule = $this->rule->empty();
        $rule = new OrRule([$childRule]);

        $expected = [
            '@rule' => 'or',
            'rules' => [
                ['@rule' => \get_class($childRule)],
            ],
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $data = [
            '@rule' => 'or',
            'rules' => [5],
            'sample' => 5,
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(OrRule::class, $rule);
        self::assertSame(5, $rule->getSample());
        self::assertCount(1, $rule->getRules());
        self::assertInstanceOf(Rule::class, $rule->getRules()[0]);
    }

    public function supportedValuesProvider()
    {
        $this->setUp();
        $rule = $this->rule->empty();

        return [
            [false, []],
            [false, 100],
            [false, 1.0],
            [false, 'string'],
            [false, true],
            [false, false],
            [false, null],
            [false, new class() {
            }],
            [false, new \stdClass()],
            [false, $rule],
            [false, [100]],
            [false, [1.0]],
            [false, ['string']],
            [false, [true]],
            [false, [false]],
            [false, [null]],
            [false, [new class() {
            }]],
            [false, [new \stdClass()]],
            [true, [$rule]],
            [true, [['key' => $rule]]],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     *
     * @param mixed $value
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(OrRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            $this->expectException(\Throwable::class);
        }

        $method = new \ReflectionMethod(OrRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testMatch()
    {
        $matching = $this->rule->matching();
        $mismatching = $this->rule->notMatching();

        $rule = new OrRule([$mismatching, $matching]);

        $rule->assertMatch('No Mismatch is thrown');

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }

    public function testMismatch()
    {
        $mismatching = $this->rule->notMatching();

        $rule = new OrRule([$mismatching, $mismatching]);

        try {
            $rule->assertMatch('A Mismatch should be thrown');
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertEquals(2, \count($mismatches));

            return;
        }

        self::fail('An MismatchCollection should been thrown');
    }

    public function testExpectingArrayButGotString()
    {
        $matching = $this->rule->matching();

        $rule = new OrRule([['key' => $matching], ['key' => $matching]]);

        try {
            $rule->assertMatch('This value is a string');
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertEquals(2, \count($mismatches->toArrayFlat()));

            return;
        }

        self::fail('An MismatchCollection should been thrown');
    }

    public function testMatchArray()
    {
        $matching = $this->rule->matching();
        $mismatching = $this->rule->notMatching();

        $rule = new OrRule([['key' => $mismatching], $matching]);

        $rule->assertMatch(['This value is matching the second Rule']);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }

    public function testMatchArrayButMissingKeys()
    {
        $matching = $this->rule->matching();

        $rule = new OrRule([['a1' => $matching, 'a2' => $matching], ['b3' => $matching]]);

        try {
            $rule->assertMatch([]);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertEquals(3, \count($mismatches->toArrayFlat()));

            return;
        }

        self::fail('An MismatchCollection should been thrown');
    }

    public function testMismatchArray()
    {
        $matching = $this->rule->matching();
        $mismatching = $this->rule->notMatching();

        $rule = new OrRule([[$matching, $mismatching]]);

        try {
            $rule->assertMatch(['A Mismatch should be thrown']);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertEquals(1, \count($mismatches));

            return;
        }

        self::fail('An MismatchCollection should been thrown');
    }
}
