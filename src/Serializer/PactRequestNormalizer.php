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

namespace PHPacto\Serializer;

use PHPacto\Encoder\HeadersEncoder;
use PHPacto\Matcher\Mismatches;
use PHPacto\Matcher\Rules\Rule;
use PHPacto\Matcher\Rules\StringEqualsRule;
use PHPacto\PactRequest;
use PHPacto\PactRequestInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class PactRequestNormalizer extends AbstractNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            PactRequestInterface::class => true,
            PactRequestInterface::class.'[]' => true,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PactRequestInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_array($data) && PactRequestInterface::class === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if (!$object instanceof PactRequestInterface) {
            throw new InvalidArgumentException(sprintf('The object "%s" must implement "%s".', \get_class($object), PactRequestInterface::class));
        }

        return $this->normalizeObject($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!(\is_array($data) && PactRequestInterface::class === $type)) {
            throw new InvalidArgumentException(sprintf('Data must be array type and class equal to "%s".', PactRequestInterface::class));
        }

        return $this->denormalizeArray($data, PactRequest::class, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function isAllowedAttribute(object|string $classOrObject, string $attribute, ?string $format = null, array $context = []): bool
    {
        if (\in_array($attribute, ['sample', 'sampleHeaders', 'sampleBody'], true)) {
            return false;
        }

        return parent::isAllowedAttribute($classOrObject, $attribute, $format, $context);
    }

    private function normalizeObject(PactRequestInterface $object, $format = null, array $context = [])
    {
        if (!isset($context['cache_key'])) {
            $context['cache_key'] = false;
        }

        $data = [];

        $attributes = $this->getAttributes($object, $format, $context);

        foreach ($attributes as $attribute) {
            $attributeValue = $this->getAttributeValue($object, $attribute, $format, $context);

            if ($this->nameConverter) {
                $attribute = $this->nameConverter->normalize($attribute);
            }

            if (null !== $attributeValue && !is_scalar($attributeValue)) {
                $data[$attribute] = $this->serializer->normalize($attributeValue, $format, $this->createChildContext($context, $attribute, $format));
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
            $context['cache_key'] = false;
        }

        try {
            if (\is_string($data['path'])) {
                $data['path'] = new StringEqualsRule($data['path']);
            } else {
                $data['path'] = $this->serializer->denormalize($data['path'], Rule::class, $format, $this->createChildContext($context, 'path', $format));
            }
        } catch (Mismatches\Mismatch $e) {
            $mismatches['PATH'] = $e;
        }

        try {
            if (\is_string($data['method'])) {
                $data['method'] = new StringEqualsRule(strtoupper($data['method']));
            } else {
                $data['method'] = $this->serializer->denormalize($data['method'], Rule::class, $format, $this->createChildContext($context, 'method', $format));
            }
        } catch (Mismatches\Mismatch $e) {
            $mismatches['METHOD'] = $e;
        }

        try {
            if (\array_key_exists('headers', $data) && \is_array($data['headers'])) {
                $headers = [];

                foreach ($data['headers'] as $headerKey => $headerValue) {
                    $headerKey = HeadersEncoder::normalizeName($headerKey);
                    $headers[$headerKey] = $this->serializer->denormalize($headerValue, Rule::class, $format, $this->createChildContext($context, 'headers.' . $headerKey, $format));
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
                $data['body'] = $this->serializer->denormalize($data['body'], Rule::class, $format, $this->createChildContext($context, 'body', $format));
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
}
