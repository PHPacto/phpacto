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

namespace Bigfoot\PHPacto\Factory;

use Bigfoot\PHPacto\Serializer\PactNormalizer;
use Bigfoot\PHPacto\Serializer\PactRequestNormalizer;
use Bigfoot\PHPacto\Serializer\PactResponseNormalizer;
use Bigfoot\PHPacto\Serializer\RuleMap;
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

    /**
     * @var RuleMap
     */
    private static $ruleMap;

    private function __construct()
    {
    }

    public static function getInstance(): Serializer
    {
        if (!self::$instance) {
            self::$ruleMap = new RuleMap();

            self::$instance = new Serializer(
                self::getNormalizers(),
                self::getEncoders()
            );
        }

        return self::$instance;
    }

    public static function getRuleMap(): RuleMap
    {
        return self::$ruleMap;
    }

    /**
     * @return NormalizerInterface[]
     */
    protected static function getNormalizers(): array
    {
        $nameConverter = new CamelCaseToSnakeCaseNameConverter();

        return [
            new RuleNormalizer(self::$ruleMap, null, $nameConverter),
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
            new JsonEncoder(new JsonEncode([JsonEncode::OPTIONS => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE]), new JsonDecode(true)),
            new YamlEncoder(null, null, ['yaml_inline' => 999, 'yaml_flags' => Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK, 'allow_extra_attributes' => false]),
        ];
    }
}
