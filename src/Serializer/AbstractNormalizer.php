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

namespace PHPacto\Serializer;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyWriteInfo;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

abstract class AbstractNormalizer extends AbstractObjectNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    private static $reflectionCache = [];
    private static $isReadableCache = [];
    private static $isWritableCache = [];

    protected PropertyAccessorInterface $propertyAccessor;
    protected $propertyInfoExtractor;
    private $writeInfoExtractor;

    private readonly \Closure $objectClassResolver;

    public function __construct(?ClassMetadataFactoryInterface $classMetadataFactory = null, ?NameConverterInterface $nameConverter = null, ?PropertyAccessorInterface $propertyAccessor = null, ?PropertyTypeExtractorInterface $propertyTypeExtractor = null, ?ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null, ?callable $objectClassResolver = null, array $defaultContext = [], ?PropertyInfoExtractorInterface $propertyInfoExtractor = null)
    {
        if (!class_exists(PropertyAccess::class)) {
            throw new LogicException('The ObjectNormalizer class requires the "PropertyAccess" component. Try running "composer require symfony/property-access".');
        }

        parent::__construct($classMetadataFactory, $nameConverter, $propertyTypeExtractor, $classDiscriminatorResolver, $objectClassResolver, $defaultContext);

        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();

        $this->objectClassResolver = ($objectClassResolver ?? static fn ($class) => \is_object($class) ? $class::class : $class)(...);
        $this->propertyInfoExtractor = $propertyInfoExtractor ?: new ReflectionExtractor();
        $this->writeInfoExtractor = new ReflectionExtractor();
    }

    /**
     * {@inheritdoc}
     */
    protected function extractAttributes(object $object, ?string $format = null, array $context = []): array
    {
        if (\stdClass::class === $object::class) {
            return array_keys((array) $object);
        }

        // If not using groups, detect manually
        $attributes = [];

        // methods
        $class = ($this->objectClassResolver)($object);
        $reflClass = new \ReflectionClass($class);

        foreach ($reflClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflMethod) {
            if (
                0 !== $reflMethod->getNumberOfRequiredParameters()
                || $reflMethod->isStatic()
                || $reflMethod->isConstructor()
                || $reflMethod->isDestructor()
            ) {
                continue;
            }

            $name = $reflMethod->name;
            $attributeName = null;

            if (3 < \strlen($name) && match ($name[0]) {
                'g' => str_starts_with($name, 'get'),
                'h' => str_starts_with($name, 'has'),
                'c' => str_starts_with($name, 'can'),
                default => false,
            }) {
                // getters, hassers and canners
                $attributeName = substr($name, 3);

                if (!$reflClass->hasProperty($attributeName)) {
                    $attributeName = lcfirst($attributeName);
                }
            } elseif ('is' !== $name && str_starts_with($name, 'is')) {
                // issers
                $attributeName = substr($name, 2);

                if (!$reflClass->hasProperty($attributeName)) {
                    $attributeName = lcfirst($attributeName);
                }
            }

            if (null !== $attributeName && $this->isAllowedAttribute($object, $attributeName, $format, $context)) {
                $attributes[$attributeName] = true;
            }
        }

        // properties
        foreach ($reflClass->getProperties() as $reflProperty) {
            if (!$reflProperty->isPublic()) {
                continue;
            }

            if ($reflProperty->isStatic() || !$this->isAllowedAttribute($object, $reflProperty->name, $format, $context)) {
                continue;
            }

            $attributes[$reflProperty->name] = true;
        }

        return array_keys($attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeValue(object $object, string $attribute, ?string $format = null, array $context = []): mixed
    {
        $mapping = $this->classDiscriminatorResolver?->getMappingForMappedObject($object);

        return $attribute === $mapping?->getTypeProperty()
            ? $mapping
            : $this->propertyAccessor->getValue($object, $attribute);
    }

    /**
     * {@inheritdoc}
     */
    protected function setAttributeValue(object $object, string $attribute, mixed $value, ?string $format = null, array $context = []): void
    {
        try {
            $this->propertyAccessor->setValue($object, $attribute, $value);
        } catch (NoSuchPropertyException) {
            // Properties not found are ignored
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedAttributes(string|object $classOrObject, array $context, bool $attributesAsString = false): array|bool
    {
        if (false === $allowedAttributes = parent::getAllowedAttributes($classOrObject, $context, $attributesAsString)) {
            return false;
        }

        if (null !== $this->classDiscriminatorResolver) {
            $class = \is_object($classOrObject) ? $classOrObject::class : $classOrObject;
            if (null !== $discriminatorMapping = $this->classDiscriminatorResolver->getMappingForMappedObject($classOrObject)) {
                $allowedAttributes[] = $attributesAsString ? $discriminatorMapping->getTypeProperty() : new AttributeMetadata($discriminatorMapping->getTypeProperty());
            }

            if (null !== $discriminatorMapping = $this->classDiscriminatorResolver->getMappingForClass($class)) {
                $attributes = [];
                foreach ($discriminatorMapping->getTypesMapping() as $mappedClass) {
                    $attributes[] = parent::getAllowedAttributes($mappedClass, $context, $attributesAsString);
                }
                $allowedAttributes = array_merge($allowedAttributes, ...$attributes);
            }
        }

        return $allowedAttributes;
    }

    /**
     * {@inheritdoc}
     */
    protected function isAllowedAttribute(object|string $classOrObject, string $attribute, ?string $format = null, array $context = []): bool
    {
        if (!parent::isAllowedAttribute($classOrObject, $attribute, $format, $context)) {
            return false;
        }

        $class = \is_object($classOrObject) ? \get_class($classOrObject) : $classOrObject;

        if ($context['_read_attributes'] ?? true) {
            if (!isset(self::$isReadableCache[$class.$attribute])) {
                self::$isReadableCache[$class.$attribute] = (\is_object($classOrObject) && $this->propertyAccessor->isReadable($classOrObject, $attribute)) || $this->propertyInfoExtractor->isReadable($class, $attribute) || $this->hasAttributeAccessorMethod($class, $attribute);
            }

            return self::$isReadableCache[$class.$attribute];
        }

        if (!isset(self::$isWritableCache[$class.$attribute])) {
            if (str_contains($attribute, '.')) {
                self::$isWritableCache[$class.$attribute] = true;
            } else {
                self::$isWritableCache[$class.$attribute] = $this->propertyInfoExtractor->isWritable($class, $attribute)
                    || (($writeInfo = $this->writeInfoExtractor->getWriteInfo($class, $attribute)) && PropertyWriteInfo::TYPE_NONE !== $writeInfo->getType());
            }
        }

        return self::$isWritableCache[$class.$attribute];
    }

    private function hasAttributeAccessorMethod(string $class, string $attribute): bool
    {
        if (!isset(self::$reflectionCache[$class])) {
            self::$reflectionCache[$class] = new \ReflectionClass($class);
        }

        $reflection = self::$reflectionCache[$class];

        if (!$reflection->hasMethod($attribute)) {
            return false;
        }

        $method = $reflection->getMethod($attribute);

        return !$method->isStatic()
            && !$method->getAttributes(Ignore::class)
            && !$method->getNumberOfRequiredParameters();
    }
}
