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

class UrlRuleTest extends SerializerAwareTestCase
{
    public function matchesProvider()
    {
        return [
            [false, '', ''],
            [false, '', 'path'],
            [true, '', '/path'],
            [false, '', '?query'],
            [true, '', '/?query'],
            [false, '', '//hostname?query'],
            [true, '', '//hostname/?query=b'],
            [true, '', '//hostname:80/'],
            [true, '', 'http://hostname/'],
            [true, '', 'https://hostname:433/'],
            [true, '', '/{param1}/{param2}'],
        ];
    }

    /**
     * @dataProvider matchesProvider
     *
     * @param mixed $ruleValue
     * @param mixed $testValue
     */
    public function testMatch(bool $shouldMatch, $ruleValue, $testValue)
    {
        $rule = new UrlRule($ruleValue, [], null);

        if (!$shouldMatch) {
            $this->expectException(Mismatches\TypeMismatch::class);
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown if matching');
    }
}
