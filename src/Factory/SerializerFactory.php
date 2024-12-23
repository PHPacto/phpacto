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

namespace PHPacto\Factory;

use PHPacto\Serializer\ClassResolver;
use PHPacto\Serializer\JsonEncoder;
use PHPacto\Serializer\PactNormalizer;
use PHPacto\Serializer\PactRequestNormalizer;
use PHPacto\Serializer\PactResponseNormalizer;
use PHPacto\Serializer\RuleMap;
use PHPacto\Serializer\RuleNormalizer;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Yaml;

final class SerializerFactory
{
    /**
     * @var Serializer
     */
    private static $instance;

    /**
     * @var RuleMap
     */
    private static $ruleMap;

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
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $nameConverter = new CamelCaseToSnakeCaseNameConverter();
        $discriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);
        $resolver = new ClassResolver();

        $context = [
            DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
            ObjectNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
            ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ObjectNormalizer::SKIP_NULL_VALUES => true,
        ];

        return [
            //new UnwrappingDenormalizer(),
            //new ArrayDenormalizer(),
            new RuleNormalizer(self::$ruleMap, classMetadataFactory: $classMetadataFactory, nameConverter: $nameConverter, defaultContext: $context),
            new PactResponseNormalizer(classMetadataFactory: $classMetadataFactory, nameConverter: $nameConverter, defaultContext: $context),
            new PactRequestNormalizer(classMetadataFactory: $classMetadataFactory, nameConverter: $nameConverter, defaultContext: $context),
            new PactNormalizer(classMetadataFactory: $classMetadataFactory, nameConverter: $nameConverter, defaultContext: $context),
            /*new ObjectNormalizer(
                classMetadataFactory: $classMetadataFactory,
                nameConverter: $nameConverter,
                defaultContext: $defaultContext,
                classDiscriminatorResolver: $discriminator,
                objectClassResolver: $resolver,
            ),
            new GetSetMethodNormalizer($classMetadataFactory, $nameConverter),
            new JsonSerializableNormalizer($classMetadataFactory, $nameConverter)*/
        ];
    }

    /**
     * @return EncoderInterface[]
     */
    protected static function getEncoders(): array
    {
        return [
            new JsonEncoder(),
            new YamlEncoder(null, null, [
                YamlEncoder::YAML_INLINE => 999,
                YamlEncoder::YAML_FLAGS => Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
                'allow_extra_attributes' => false
            ]),
        ];
    }
}
