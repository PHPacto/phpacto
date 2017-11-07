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

class AbstractStringRuleTest extends RuleAbstractTest
{
    public function test_it_is_not_case_sensitive_by_default()
    {
        $rule = $this->getMockBuilder(AbstractStringRule::class)
            ->setConstructorArgs(['value', 'sample'])
            ->setMethodsExcept(['isCaseSensitive'])
            ->getMock();

        self::assertFalse($rule->isCaseSensitive());
    }

    public function test_it_is_case_sensitive()
    {
        $rule = $this->getMockBuilder(AbstractStringRule::class)
            ->setConstructorArgs(['value', 'sample', true])
            ->setMethodsExcept(['isCaseSensitive'])
            ->getMock();

        self::assertTrue($rule->isCaseSensitive());
    }

    public function test_it_is_normalizable()
    {
        $rule = $this->getMockBuilder(AbstractStringRule::class)
            ->setConstructorArgs(['value', 'sample', true])
            ->setMethods(null)
            ->getMock();

        $expected = [
            '@rule' => get_class($rule),
            'value' => 'value',
            'sample' => 'sample',
            'caseSensitive' => true,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }
}
