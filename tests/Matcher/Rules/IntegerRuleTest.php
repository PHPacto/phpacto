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

class IntegerRuleTest extends SerializerAwareTestCase
{
    public function test_it_has_a_default_sample()
    {
        $rule = new IntegerRule();

        self::assertEquals(0, $rule->getSample());
    }

    public function test_it_is_normalizable()
    {
        $rule = new IntegerRule(5);

        $expected = [
            '_rule' => 'integer',
            'sample' => 5,
        ];

        self::assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_denormalizable()
    {
        $data = [
            '_rule' => 'integer',
            'sample' => 5,
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(IntegerRule::class, $rule);
        self::assertSame(5, $rule->getSample());
    }

    public function supportedValuesProvider()
    {
        return [
            [true, 100],
            [false, 10.0],
            [false, 'string'],
            [false, true],
            [false, false],
            [false, null],
            [false, []],
            [false, new class () {
            }],
            [false, new \stdClass()],
        ];
    }
}
