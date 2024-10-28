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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 */

namespace PHPacto\Matcher\Rules;

use PHPacto\Serializer\SerializerAwareTestCase;

class StringRuleTest extends SerializerAwareTestCase
{
    public function test_it_has_a_default_sample()
    {
        $rule = $this->getMockBuilder(StringRule::class)
            ->setMethodsExcept(['getSample'])
            ->getMock();

        self::assertSame('', $rule->getSample());
    }

    public function test_it_is_normalizable()
    {
        $rule = new StringRule('Sample');

        $expected = 'Sample';

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $data = [
            '_rule' => 'string',
            'sample' => 'Sample',
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(StringRule::class, $rule);
        self::assertSame('Sample', $rule->getSample());
    }
    public function test_it_is_case_sensitive_by_default()
    {
        $rule = $this->getMockBuilder(StringRule::class)
            ->setConstructorArgs(['sample'])
            ->setMethodsExcept(['isCaseSensitive'])
            ->getMock();

        self::assertTrue($rule->isCaseSensitive());
    }

    public function test_it_can_be_case_insensitive()
    {
        $rule = $this->getMockBuilder(StringRule::class)
            ->setConstructorArgs(['sample', false])
            ->setMethodsExcept(['isCaseSensitive'])
            ->getMock();

        self::assertFalse($rule->isCaseSensitive());
    }
}
