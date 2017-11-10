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

class OrRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $childRule = $this->rule->empty();
        $rule = new OrRule([$childRule]);

        $expected = [
            '@rule' => OrRule::class,
            'rules' => [['@rule' => get_class($childRule)]],
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        $rule = self::getRuleMockFactory()->empty();

        return [
            [true, []],
            [false, 100],
            [false, 1.0],
            [false, 'string'],
            [false, true],
            [false, false],
            [false, null],
            [false, new class() {}],
            [false, new \stdClass()],
            [false, $rule],
            [false, [[]]],
            [false, [100]],
            [false, [1.0]],
            [false, ['string']],
            [false, [true]],
            [false, [false]],
            [false, [null]],
            [false, [new class() {}]],
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
        $mockOk = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockMismatch = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule = new OrRule([$mockMismatch, $mockOk]);

        $rule->assertMatch('No Mismatch is thrown');

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }

    public function testMismatch()
    {
        $mockMismatch = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockMismatch
            ->method('assertMatch')
            ->willThrowException(new Mismatches\ValueMismatch('A mismatch is expected', true, false));

        $rule = new OrRule([$mockMismatch, $mockMismatch]);

        $this->expectException(Mismatches\MismatchCollection::class);

        $rule->assertMatch('A Mismatch should be thrown');
    }

    /**
     * @depends testMismatch
     */
    public function testMismatchCount()
    {
        try {
            $this->testMismatch();
        } catch (Mismatches\MismatchCollection $e) {
            self::assertEquals(2, count($e));

            throw $e;
        }
    }

    /**
     * @depends testMismatch
     */
    public function testGetSample()
    {
        $ruleA = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ruleA
            ->method('getSample')
            ->willReturn('A');

        $ruleB = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ruleB
            ->method('getSample')
            ->willReturn('B');

        $rule = new OrRule([$ruleA, $ruleB]);

        self::assertContains($rule->getSample(), ['A', 'B']);
    }
}
