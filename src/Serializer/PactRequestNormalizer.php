<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2019  Damian Długosz
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

use Bigfoot\PHPacto\Encoder\HeadersEncoder;
use Bigfoot\PHPacto\Matcher\Mismatches;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Bigfoot\PHPacto\Matcher\Rules\StringEqualsRule;
use Bigfoot\PHPacto\PactRequest;
use Bigfoot\PHPacto\PactRequestInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PactRequestNormalizer extends GetSetMethodNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PactRequestInterface && self::isFormatSupported($format);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return \is_array($data) && PactRequestInterface::class === $type && self::isFormatSupported($format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof PactRequestInterface) {
            throw new InvalidArgumentException(sprintf('The object "%s" must implement "%s".', \get_class($object), PactRequestInterface::class));
        }

        return $this->normalizeObject($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!(\is_array($data) && PactRequestInterface::class === $class)) {
            throw new InvalidArgumentException(sprintf('Data must be array type and class equal to "%s".', PactRequestInterface::class));
        }

        return $this->denormalizeArray($data, PactRequest::class, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function isAllowedAttribute($classOrObject, $attribute, $format = null, array $context = [])
    {
        if (\in_array($attribute, ['sample', 'sampleHeaders', 'sampleBody'], true)) {
            return false;
        }

        return parent::isAllowedAttribute($classOrObject, $attribute, $format, $context);
    }

    private static function isFormatSupported(?string $format): bool
    {
        return \in_array($format, [null, 'json', 'yaml'], true);
    }

    private function normalizeObject(PactRequestInterface $object, $format = null, array $context = [])
    {
        if (!isset($context['cache_key'])) {
            $context['cache_key'] = $this->getCacheKey($format, $context);
        }

        $data = [];

        $attributes = $this->getAttributes($object, $format, $context);

        foreach ($attributes as $attribute) {
            $attributeValue = $this->getAttributeValue($object, $attribute, $format, $context);

            if ($this->nameConverter) {
                $attribute = $this->nameConverter->normalize($attribute);
            }

            if (null !== $attributeValue && !is_scalar($attributeValue)) {
                $data[$attribute] = $this->recursiveNormalization($attributeValue, $format, $this->createChildContext($context, $attribute));
            } else {
                $data[$attribute] = $attributeValue;
            }
        }

        $methodRule = $object->getMethod();
        if ($methodRule instanceof StringEqualsRule) {
            $data['method'] = $methodRule->getValue();
        }

        $pathRule = $object->getPath();
        if ($pathRule instanceof StringEqualsRule) {
            $data['path'] = $pathRule->getValue();
        }

        if (empty($data['body'])) {
            unset($data['body']);
        }

        if (empty($data['headers'])) {
            unset($data['headers']);
        }

        return $data;
    }

    private function denormalizeArray($data, $class, $format = null, array $context = []): PactRequestInterface
    {
        $mismatches = [];

        if (!isset($context['cache_key'])) {
            $context['cache_key'] = $this->getCacheKey($format, $context);
        }

        try {
            if (\is_string($data['path'])) {
                $data['path'] = new StringEqualsRule($data['path']);
            } else {
                $data['path'] = $this->recursiveDenormalization($data['path'], Rule::class, $format, $this->createChildContext($context, 'path'));
            }
        } catch (Mismatches\Mismatch $e) {
            $mismatches['PATH'] = $e;
        }

        try {
            if (\is_string($data['method'])) {
                $data['method'] = new StringEqualsRule(strtoupper($data['method']));
            } else {
                $data['method'] = $this->recursiveDenormalization($data['method'], Rule::class, $format, $this->createChildContext($context, 'method'));
            }
        } catch (Mismatches\Mismatch $e) {
            $mismatches['METHOD'] = $e;
        }

        try {
            if (\array_key_exists('headers', $data) && \is_array($data['headers'])) {
                $data['headers'] = HeadersEncoder::decode($data['headers']);
                $headers = [];

                foreach ($data['headers'] as $headerKey => $headerValue) {
                    $headers[$headerKey] = $this->recursiveDenormalization($headerValue, Rule::class, $format, $this->createChildContext($context, 'headers.' . $headerKey));
                }
                $data['headers'] = $headers;
            } else {
                $data['headers'] = [];
            }
        } catch (Mismatches\Mismatch $e) {
            $mismatches['HEADERS'] = $e;
        }

        try {
            if (isset($data['body'])) {
                $data['body'] = $this->recursiveDenormalization($data['body'], Rule::class, $format, $this->createChildContext($context, 'body'));
            }
        } catch (Mismatches\Mismatch $e) {
            $mismatches['BODY'] = $e;
        }

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches, 'There are {{ count }} errors');
        }

        $object = new PactRequest($data['method'], $data['path'], $data['headers'], $data['body'] ?? null);

        return $object;
    }

    private function recursiveNormalization($data, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new LogicException('Cannot normalize data because the injected serializer is not a normalizer');
        }

        return $this->serializer->normalize($data, $format, $context);
    }

    private function recursiveDenormalization($data, $class, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof DenormalizerInterface) {
            throw new LogicException('Cannot denormalize data because the injected serializer is not a normalizer');
        }

        return $this->serializer->denormalize($data, $class, $format, $context);
    }

    /**
     * Gets the cache key to use.
     *
     * @param string|null $format
     * @param array       $context
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

    /**
     * Gets attributes to normalize using groups.
     *
     * @param string|object $classOrObject
     * @param array         $context
     * @param bool          $attributesAsString If false, return an array of {@link AttributeMetadataInterface}
     *
     * @throws LogicException if the 'allow_extra_attributes' context variable is false and no class metadata factory is provided
     *
     * @return string[]|AttributeMetadataInterface[]|bool
     */
//    protected function getAllowedAttributes($classOrObject, array $context, $attributesAsString = false)
//    {
//        return [
//            'body',
//            'headers',
//        ];
//    }
}
