<?php

/*
 * This file is part of PHPacto
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

class AbstractRuleTest extends RuleAbstractTest
{
    public function test_it_has_a_value_and_a_sample()
    {
        // Don't use RuleMockFactory because I want to test constructor arguments

        /** @var Rule $rule */
        $rule = $this->getMockBuilder(AbstractRule::class)
            ->setConstructorArgs(['value', 'sample'])
            ->setMethodsExcept(['getValue', 'getSample'])
            ->getMock();

        self::assertEquals('value', $rule->getValue());
        self::assertEquals('sample', $rule->getSample());
    }

    public function test_it_is_normalizable()
    {
        $rule = $this->rule->hasValueAndSample('value', 'sample');

        $expected = [
            '@rule' => get_class($rule),
            'value' => 'value',
            'sample' => 'sample',
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }
}
