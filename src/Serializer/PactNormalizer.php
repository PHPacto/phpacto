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

namespace Bigfoot\PHPacto\Serializer;

use Bigfoot\PHPacto\Matcher\Mismatches;
use Bigfoot\PHPacto\Pact;
use Bigfoot\PHPacto\PactInterface;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PactNormalizer extends GetSetMethodNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PactInterface && self::isFormatSupported($format);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return \is_array($data) && PactInterface::class === $type && self::isFormatSupported($format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof PactInterface) {
            throw new InvalidArgumentException(sprintf('The object "%s" must implement "%s".', \get_class($object), PactInterface::class));
        }

        return $this->normalizePactObject($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!(\is_array($data) && PactInterface::class === $class)) {
            throw new InvalidArgumentException(sprintf('Data must be array type and class equal to "%s".', $class, PactInterface::class));
        }

        return $this->denormalizeArray($data, Pact::class, $format, $context);
    }

    protected function instantiateObject(array &$data, $class, array &$context, \ReflectionClass $reflectionClass, $allowedAttributes, string $format = null)
    {
        $constructor = $this->getConstructor($data, $class, $context, $reflectionClass, $allowedAttributes);
        if ($constructor) {
            $constructorParameters = $constructor->getParameters();

            $mismatches = [];
            $params = [];
            foreach ($constructorParameters as $constructorParameter) {
                $paramName = $constructorParameter->name;
                $key = $this->nameConverter ? $this->nameConverter->normalize($paramName) : $paramName;

                $allowed = false === $allowedAttributes || \in_array($paramName, $allowedAttributes, true);
                $ignored = !$this->isAllowedAttribute($class, $paramName, $format, $context);
                if ($constructorParameter->isVariadic()) {
                    if ($allowed && !$ignored && (isset($data[$key]) || \array_key_exists($key, $data))) {
                        if (!\is_array($data[$paramName])) {
                            throw new RuntimeException(sprintf('Cannot create an instance of %s from serialized data because the variadic parameter %s can only accept an array.', $class, $constructorParameter->name));
                        }

                        $params = array_merge($params, $data[$paramName]);
                    }
                } elseif ($allowed && !$ignored && (isset($data[$key]) || \array_key_exists($key, $data))) {
                    $parameterData = $data[$key];
                    if (null === $parameterData && $constructorParameter->allowsNull()) {
                        $params[] = null;
                        // Don't run set for a parameter passed to the constructor
                        unset($data[$key]);
                        continue;
                    }
                    try {
                        if (null !== $constructorParameter->getClass()) {
                            if (!$this->serializer instanceof DenormalizerInterface) {
                                throw new LogicException(sprintf('Cannot create an instance of %s from serialized data because the serializer inject in "%s" is not a denormalizer', $constructorParameter->getClass(), static::class));
                            }
                            $parameterClass = $constructorParameter->getClass()->getName();
                            $parameterData = $this->serializer->denormalize($parameterData, $parameterClass, $format, $this->createChildContext($context, $paramName, $format));
                        }
                    } catch (Mismatches\Mismatch $e) {
                        $mismatches[strtoupper($key)] = $e;
                    } catch (\ReflectionException $e) {
                        throw new RuntimeException(sprintf('Could not determine the class of the parameter "%s".', $key), 0, $e);
                    } catch (MissingConstructorArgumentsException $e) {
                        if (!$constructorParameter->getType()->allowsNull()) {
                            throw $e;
                        }
                        $parameterData = null;
                    }

                    // Don't run set for a parameter passed to the constructor
                    $params[] = $parameterData;
                    unset($data[$key]);
                } elseif ($constructorParameter->isDefaultValueAvailable()) {
                    $params[] = $constructorParameter->getDefaultValue();
                } else {
                    $message = sprintf('Cannot create an instance of %s from serialized data because its constructor requires parameter "%s" to be present.', $class, $constructorParameter->name);

                    // MissingConstructorArgumentsException added on Sf 4.1
                    if (class_exists(MissingConstructorArgumentsException::class)) {
                        throw new MissingConstructorArgumentsException($message);
                    }

                    throw new RuntimeException($message);
                }
            }

            if ($mismatches) {
                throw new Mismatches\MismatchCollection($mismatches, 'There are {{ count }} errors');
            }

            if ($constructor->isConstructor()) {
                return $reflectionClass->newInstanceArgs($params);
            }

            return $constructor->invokeArgs(null, $params);
        }

        return new $class();
    }

    private static function isFormatSupported(?string $format): bool
    {
        return \in_array($format, [null, 'json', 'yaml'], true);
    }

    private function normalizePactObject(PactInterface $object, $format = null, array $context = [])
    {
        if (!isset($context['cache_key'])) {
            $context['cache_key'] = $this->getCacheKey($format, $context);
        }

        $data = [];

        //$attributes = $this->getAttributes($object, $format, $context);
        $attributes = ['version', 'description', 'request', 'response'];

        foreach ($attributes as $attribute) {
            $attributeValue = $this->getAttributeValue($object, $attribute, $format, $context);

            if ($this->nameConverter) {
                $attribute = $this->nameConverter->normalize($attribute);
            }

            if (null !== $attributeValue && !is_scalar($attributeValue)) {
                $data[$attribute] = $this->recursiveNormalization($attributeValue, $format, $this->createChildContext($context, $attribute, $format));
            } else {
                $data[$attribute] = $attributeValue;
            }
        }

        return $data;
    }

    private function denormalizeArray($data, $class, $format = null, array $context = []): PactInterface
    {
        if (!isset($context['cache_key'])) {
            $context['cache_key'] = $this->getCacheKey($format, $context);
        }

        $allowedAttributes = $this->getAllowedAttributes($class, $context, true);

        $reflectionClass = new \ReflectionClass($class);
        $object = $this->instantiateObject($data, $class, $context, $reflectionClass, $allowedAttributes, $format);

        foreach ($data as $attribute => $value) {
            if ($this->nameConverter) {
                $attribute = $this->nameConverter->denormalize($attribute);
            }

            if ((false !== $allowedAttributes && !\in_array($attribute, $allowedAttributes, true)) || !$this->isAllowedAttribute($class, $attribute, $format, $context)) {
                $extraAttributes[] = $attribute;

                continue;
            }

            try {
                $this->setAttributeValue($object, $attribute, $value, $format, $context);
            } catch (InvalidArgumentException $e) {
                throw new UnexpectedValueException($e->getMessage(), $e->getCode(), $e);
            }
        }

        if (!empty($extraAttributes)) {
            throw new ExtraAttributesException($extraAttributes);
        }

        return $object;
    }

    private function recursiveNormalization($data, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new LogicException('Cannot normalize data because the injected serializer is not a normalizer');
        }

        return $this->serializer->normalize($data, $format, $context);
    }

    /**
     * Gets the cache key to use.
     *
     * @param string|null $format
     *
     * @return bool|string
     */
    private function getCacheKey($format, array $context)
    {
        try {
            return md5($format . serialize($context));
        } catch (\Exception $exception) {
            // The context cannot be serialized, skip the cache
            return false;
        }
    }
}
