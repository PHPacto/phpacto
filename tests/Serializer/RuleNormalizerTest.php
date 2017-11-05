<?php

/*
 * This file is part of PHPacto
 * Copyright (C) 2017  Damian Długosz
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

namespace Bigfoot\PHPacto\Serializer;

use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Matcher\Rules\EachRule;
use Bigfoot\PHPacto\Matcher\Rules\EqualsRule;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Bigfoot\PHPacto\Matcher\Rules\StringEqualsRule;
use PHPUnit\Framework\TestCase;

class RuleNormalizerTest extends TestCase
{
    public function normalizationFormatProvider()
    {
        return [
            [null],
            ['json'],
            ['yaml'],
        ];
    }

    /**
     * @dataProvider normalizationFormatProvider
     */
    public function test_it_support_normalization(?string $format)
    {
        /** @var RuleNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(RuleNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization'])
            ->getMock();

        $rule = $this->createMock(Rule::class);

        self::assertTrue($normalizer->supportsNormalization($rule, $format));
    }

    /**
     * @dataProvider normalizationFormatProvider
     */
    public function test_it_support_denormalization(?string $format)
    {
        /** @var RuleNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(RuleNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization'])
            ->getMock();

        self::assertTrue($normalizer->supportsDenormalization([], Rule::class, $format));
    }

    public function test_serialize_equals()
    {
        $serializer = SerializerFactory::getInstance();

        $rule = new EqualsRule(0);

        $data = $serializer->normalize($rule);

        $rule = $serializer->denormalize($data, Rule::class);

        self::assertInstanceOf(EqualsRule::class, $rule);
    }

    public function test_serialize_string_equals()
    {
        $serializer = SerializerFactory::getInstance();

        $rule = new StringEqualsRule('s');

        $data = $serializer->normalize($rule);

        $rule = $serializer->denormalize($data, Rule::class);

        self::assertInstanceOf(StringEqualsRule::class, $rule);
    }

    public function test_serialize_string_equals_case_sensitive()
    {
        $serializer = SerializerFactory::getInstance();

        $rule = new StringEqualsRule('s', true);

        $data = $serializer->normalize($rule);

        $rule = $serializer->denormalize($data, Rule::class);

        self::assertInstanceOf(StringEqualsRule::class, $rule);
        self::assertTrue($rule->isCaseSensitive());
    }

    public function test_serialize_recursive()
    {
        $serializer = SerializerFactory::getInstance();

        $rule = new EachRule(new StringEqualsRule('a'), ['a']);

        $data = $serializer->normalize($rule);

        $rule = $serializer->denormalize($data, Rule::class);

        self::assertInstanceOf(EachRule::class, $rule);
        self::assertInstanceOf(StringEqualsRule::class, $stringRule = $rule->getValue());
    }

    public function test_serialize_array()
    {
        $serializer = SerializerFactory::getInstance();

        $rule = [new EqualsRule(0)];

        $data = $serializer->normalize($rule);

        $rule = $serializer->denormalize($data, Rule::class);

        self::assertCount(1, $rule);
        self::assertInstanceOf(EqualsRule::class, $rule[0]);
    }

    public function test_denormalize_nested_array()
    {
        $serializer = SerializerFactory::getInstance();

        $data = [1, [2]];

        $rules = $serializer->denormalize($data, Rule::class);

        self::assertCount(2, $rules);
        self::assertInstanceOf(EqualsRule::class, $rules[0]);

        self::assertCount(1, $rules[1]);
        self::assertInstanceOf(EqualsRule::class, $rules[1][0]);
    }
}
