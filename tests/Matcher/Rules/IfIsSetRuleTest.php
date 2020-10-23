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

class IfIsSetRuleTest extends SerializerAwareTestCase
{
    public function test_it_is_normalizable()
    {
        $childRule = $this->rule->empty();
        $rule = new IfIsSetRule($childRule);

        $expected = [
            '_rule' => 'ifNotNull',
            'rules' => [
                '_rule' => \get_class($childRule),
            ],
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $childRule = $this->rule->empty();

        $data = [
            '_rule' => 'ifNotNull',
            'rules' => ['_rule' => \get_class($childRule)],
            'sample' => 'any',
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(IfIsSetRule::class, $rule);
        self::assertInstanceOf(Rule::class, $rule->getRules());
        self::assertSame('any', $rule->getSample());
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
        $rule = self::getMockBuilder(IfIsSetRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            $this->expectException(\Throwable::class);
        }

        $method = new \ReflectionMethod(IfIsSetRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testMatch()
    {
        $matching = $this->rule->matching();

        $rule = new IfIsSetRule($matching);

        $rule->assertMatch('No Mismatch is thrown');
        $rule->assertMatch(null);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }

    public function testMatchArray()
    {
        $matching = $this->rule->matching();

        $rule = new IfIsSetRule([$matching]);

        $rule->assertMatch(['This value is matching the first Rule', 'Another value']);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }

    public function testMatchArrayObject()
    {
        $matching = $this->rule->matching();

        $rule = new IfIsSetRule(['key1' => $matching, 'key2' => $matching]);

        $rule->assertMatch(['key1' => 'Value 1', 'key2' => 'Value 2']);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }

    public function testMismatch()
    {
        $mismatching = $this->rule->notMatching();

        $rule = new IfIsSetRule($mismatching);

        self::expectException(Mismatches\Mismatch::class);
        $rule->assertMatch('A Mismatch should be thrown');
    }

    public function testMismatchArray()
    {
        $matching = $this->rule->matching();
        $mismatching = $this->rule->notMatching();

        $rule = new IfIsSetRule([[$matching, $mismatching]]);

        try {
            $rule->assertMatch(['A Mismatch should be thrown']);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertEquals(1, \count($mismatches));

            return;
        }

        self::fail('An MismatchCollection should been thrown');
    }

    public function testMismatchArrayMissingKey()
    {
        $matching = $this->rule->matching();

        $rule = new IfIsSetRule([
            'A' => $matching,
            'B' => $matching,
            'C' => $matching,
        ]);

        try {
            $rule->assertMatch([
                'A' => 'X',
                'B' => 'Y',
            ]);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertEquals(['C'], array_keys($mismatches->toArrayFlat()));
            self::assertInstanceOf(Mismatches\KeyNotFoundMismatch::class, $mismatches['C']);

            return;
        }
    }
}
