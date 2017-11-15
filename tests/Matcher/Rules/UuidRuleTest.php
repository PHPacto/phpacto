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

class UuidRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $rule = new UuidRule();

        $expected = [
            '@rule' => UuidRule::class,
            'sample' => '00000000-0000-0000-0000-000000000000',
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function testSampleIsMatchingRule()
    {
        $rule = self::getMockBuilder(UuidRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects(self::once())
            ->method('assertMatch')
            ->with('uuid');

        $rule->__construct('uuid');
    }

    /**
     * @depends testSampleIsMatchingRule
     */
    public function testExceptionIsTrhownIfSampleIsNotMatching()
    {
        $this->expectException(Mismatches\ValueMismatch::class);

        new UuidRule('.');
    }

    public function matchesTrueProvider()
    {
        return [
            'Empty' => [true, '00000000-0000-0000-0000-000000000000'],
            'v1' => [true, 'e4eaaaf2-d142-11e1-b3e4-080027620cdd'],
            'v3' => [true, '11a38b9a-b3da-360f-9353-a5a725514269'],
            'v4' => [true, '25769c6c-d34d-4bfe-ba98-e0ee856f3e7a'],
            'v5' => [true, 'c4a760a8-dbcf-5254-a0d9-6a4474bd1b62'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 0],
            [false, 0.1],
            [false, ''],
            [false, null],
            [false, false],
            [false, true],
        ];
    }

    /**
     * @depends testSampleIsMatchingRule
     * @dataProvider matchesTrueProvider
     * @dataProvider matchesFalseProvider
     *
     * @param mixed $testValue
     */
    public function testMatch(bool $shouldMatch, $testValue)
    {
        $rule = new UuidRule();

        if (!$shouldMatch) {
            $this->expectException(Mismatches\ValueMismatch::class);
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
