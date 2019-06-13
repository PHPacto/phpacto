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

use Bigfoot\PHPacto\Serializer\SerializerAwareTestCase;

class NumericRuleTest extends SerializerAwareTestCase
{
    public function test_it_has_a_default_sample()
    {
        $rule = $this->getMockBuilder(NumericRule::class)
            ->setMethodsExcept(['getSample'])
            ->getMock();

        self::assertSame(0.0, $rule->getSample());
    }

    public function test_it_is_normalizable()
    {
        $rule = new NumericRule(0.0);

        $expected = 0.0;

        self::assertSame($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $data = 0.0;

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(NumericRule::class, $rule);
        self::assertSame(0.0, $rule->getSample());
    }

    public function supportedValuesProvider()
    {
        return [
            [true, 100],
            [true, 10.0],
            [true, '10.0'],
            [false, 'string'],
            [false, true],
            [false, false],
            [false, null],
            [false, []],
            [false, new class() {
            }],
            [false, new \stdClass()],
        ];
    }
}
