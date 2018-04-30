<?php

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

namespace Bigfoot\PHPacto\Factory;

use Bigfoot\PHPacto\Matcher\Rules;
use Bigfoot\PHPacto\Serializer\PactNormalizer;
use Bigfoot\PHPacto\Serializer\PactRequestNormalizer;
use Bigfoot\PHPacto\Serializer\PactResponseNormalizer;
use Bigfoot\PHPacto\Serializer\RuleNormalizer;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Yaml;

abstract class SerializerFactory
{
    /**
     * @var Serializer
     */
    private static $instance;

    public static function getInstance(): Serializer
    {
        if (!self::$instance) {
            self::$instance = new Serializer(
                self::getNormalizers(),
                self::getEncoders()
            );
        }

        return self::$instance;
    }

    /**
     * @return NormalizerInterface[]
     */
    protected static function getNormalizers(): array
    {
        $nameConverter = new CamelCaseToSnakeCaseNameConverter();

        return [
            new RuleNormalizer(null, $nameConverter, self::getRuleAliases()),
            new PactResponseNormalizer(null, $nameConverter),
            new PactRequestNormalizer(null, $nameConverter),
            new PactNormalizer(null, $nameConverter),
        ];
    }

    /**
     * @return EncoderInterface[]
     */
    protected static function getEncoders(): array
    {
        return [
            new JsonEncoder(new JsonEncode(JSON_PRETTY_PRINT), new JsonDecode(true)),
            new YamlEncoder(null, null, ['yaml_inline' => 999, 'yaml_flags' => Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK, 'allow_extra_attributes' => false]),
        ];
    }

    /**
     * @return string[]
     */
    protected static function getRuleAliases(): array
    {
        return [
            'and' => Rules\AndRule::class,
            'contains' => Rules\ContainsRule::class,
            'count' => Rules\CountRule::class,
            'datetime' => Rules\DateTimeRule::class,
            'each' => Rules\EachRule::class,
            'eq' => Rules\EqualsRule::class,
            'gte' => Rules\GreaterOrEqualRule::class,
            'gt' => Rules\GreaterRule::class,
            'lte' => Rules\LowerOrEqualRule::class,
            'lt' => Rules\LowerRule::class,
            'not' => Rules\NotEqualsRule::class,
            'or' => Rules\OrRule::class,
            'regex' => Rules\RegexpRule::class,
            'str' => Rules\StringRule::class,
            'strBegins' => Rules\StringBeginsRule::class,
            'strContains' => Rules\StringContainsRule::class,
            'strEnds' => Rules\StringEndsRule::class,
            'strEq' => Rules\StringEqualsRule::class,
            'strLen' => Rules\StringLengthRule::class,
            'uuid' => Rules\UuidRule::class,
            'ver' => Rules\VersionRule::class,
        ];
    }
}
