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

namespace PHPacto\Matcher\Rules;

use PHPacto\Matcher\Mismatches;
use PHPacto\Serializer\SerializerAwareTestCase;

class EachItemRuleTest extends SerializerAwareTestCase
{
    public function test_it_has_a_default_sample()
    {
        $childRule = $this->rule->hasSample(5);
        $rule = new EachItemRule($childRule);

        self::assertEquals([5], $rule->getSample());
    }

    public function test_it_is_normalizable()
    {
        $childRule = $this->rule->empty();
        $rule = new EachItemRule($childRule, []);

        $expected = [
            '_rule' => 'each',
            'rules' => ['_rule' => \get_class($childRule)],
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $childRule = $this->rule->empty();

        $data = [
            '_rule' => 'each',
            'rules' => ['_rule' => \get_class($childRule)],
            'sample' => [],
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(EachItemRule::class, $rule);
        self::assertInstanceOf(Rule::class, $rule->getRules());
        self::assertSame([], $rule->getSample());
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
            [true, $rule],
            [false, [[]]],
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
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     *
     * @param mixed $value
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(EachItemRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            $this->expectException(Mismatches\Mismatch::class);
        }

        $method = new \ReflectionMethod(EachItemRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testExpectingArrayButGotString()
    {
        $matching = $this->rule->matching();

        $rule = new EachItemRule($matching);

        $this->expectException(Mismatches\TypeMismatch::class);

        $rule->assertMatch('This value is a string');
    }

    public function testMatch()
    {
        $childRule = $this->rule->matching();
        $childRule->method('assertMatch')
            ->with(5);

        $rule = new EachItemRule($childRule);

        $rule->assertMatch([5, 5]);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }

    public function testMatchArray()
    {
        $childRule = $this->rule->matching();
        $childRule->method('assertMatch')
            ->with(5);

        $rule = new EachItemRule(['key' => $childRule]);

        $rule->assertMatch([0 => ['key' => 5], 1 => ['key' => 5]]);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }

    public function testMismatch()
    {
        $childRule = new EqualsRule(5);

        $rule = new EachItemRule($childRule);

        try {
            $rule->assertMatch([4, 5, 6]);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertEquals(['0', '2'], array_keys($mismatches->toArrayFlat()));

            return;
        }

        self::fail('An MismatchCollection should been thrown');
    }

    public function testMatchArrayButMissingKeys()
    {
        $matching = $this->rule->matching();

        $rule = new EachItemRule([
            'A' => $matching,
            'B' => $matching,
            'C' => $matching,
        ]);

        try {
            $rule->assertMatch([
                ['B' => 'Y', 'C' => 'Z'],
                ['A' => 'X', 'C' => 'Z'],
                ['A' => 'X', 'B' => 'Y'],
            ]);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertEquals(['0.A', '1.B', '2.C'], array_keys($mismatches->toArrayFlat()));

            return;
        }

        self::fail('An MismatchCollection should been thrown');
    }

    public function testMatchArrayOfStrings()
    {
        $stringRule = new StringRule();

        $rule = new EachItemRule([
            'string' => $stringRule,
        ]);

        $rule->assertMatch([
            ['string' => 'any string value'],
        ]);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }

    public function testMismatchArray()
    {
        $matching = $this->rule->matching();
        $mismatching = $this->rule->notMatching();

        $rule = new EachItemRule([
            'A' => $matching,
            'B' => $mismatching,
            'C' => $matching,
        ]);

        try {
            $rule->assertMatch([
                ['A' => 'X', 'B' => 'Y', 'C' => 'Z'],
                ['A' => 'X', 'B' => 'Y', 'C' => 'Z'],
            ]);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertEquals(['0.B', '1.B'], array_keys($mismatches->toArrayFlat()));

            return;
        }

        self::fail('An MismatchCollection should been thrown');
    }
}
