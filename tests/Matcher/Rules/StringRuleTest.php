<?php

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

use Bigfoot\PHPacto\Serializer\SerializerAwareTestCase;

class StringRuleTest extends SerializerAwareTestCase
{
    public function test_it_is_not_case_sensitive_by_default()
    {
        $rule = $this->getMockBuilder(StringRule::class)
            ->setConstructorArgs(['sample'])
            ->setMethodsExcept(['isCaseSensitive'])
            ->getMock();

        self::assertFalse($rule->isCaseSensitive());
    }

    public function test_it_is_can_be_case_sensitive()
    {
        $rule = $this->getMockBuilder(StringRule::class)
            ->setConstructorArgs(['sample', true])
            ->setMethodsExcept(['isCaseSensitive'])
            ->getMock();

        self::assertTrue($rule->isCaseSensitive());
    }

    public function test_sample_is_lower_cased_if_case_insensitive()
    {
        $rule = $this->getMockBuilder(StringRule::class)
            ->setConstructorArgs(['SAMPLE'])
            ->setMethodsExcept(['getSample'])
            ->getMock();

        self::assertEquals('sample', $rule->getSample());
    }

    public function test_it_has_a_default_sample()
    {
        $rule = $this->getMockBuilder(StringRule::class)
            ->setMethodsExcept(['getSample'])
            ->getMock();

        self::assertEquals('', $rule->getSample());
    }

    public function test_it_is_normalizable()
    {
        $rule = new StringRule('Sample', true);

        $expected = [
            '@rule' => 'string',
            'case_sensitive' => true,
            'sample' => 'Sample',
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $data = [
            '@rule' => 'string',
            'case_sensitive' => true,
            'sample' => 'Sample',
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(StringRule::class, $rule);
        self::assertSame('Sample', $rule->getSample());
        self::assertTrue($rule->isCaseSensitive());
    }
}
