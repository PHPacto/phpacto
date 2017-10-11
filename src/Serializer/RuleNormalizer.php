<?php

namespace Bigfoot\PHPacto\Serializer;

use Bigfoot\PHPacto\Matcher\Rules\EqualsRule;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Bigfoot\PHPacto\Matcher\Rules\StringEqualsRule;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RuleNormalizer extends GetSetMethodNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @var string[]
     */
    private $ruleAliases;

    public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, array $ruleAliases = [])
    {
        parent::__construct($classMetadataFactory, $nameConverter);

        $this->ruleAliases = $ruleAliases;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && self::isRule(get_class($data)) && self::isFormatSupported($format);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return self::isRule($type) && self::isFormatSupported($format) && (is_null($data) || is_array($data) || is_scalar($data));
    }

    private static function isRule(string $class): bool
    {
        $class = rtrim($class, '[]');

        return $class === Rule::class || is_subclass_of($class, Rule::class);
    }

    private static function isFormatSupported(?string $format): bool
    {
        return in_array($format, [null, 'json', 'yaml'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof Rule) {
            throw new InvalidArgumentException(sprintf('The object "%s" must implement "%s".', get_class($object), Rule::class));
        }

        if ($this->isCircularReference($object, $context)) {
            return $this->handleCircularReference($object);
        }

        if ($object instanceof EqualsRule || ($object instanceof StringEqualsRule && $object->isCaseSensitive())) {
            return $this->recursiveNormalization($object->getValue(), $format, $this->createChildContext($context, 'value'));
        }

        return $this->normalizeRuleObject($object, $format, $context);
    }

    private function normalizeRuleObject(Rule $object, $format = null, array $context = [])
    {
        if (!isset($context['cache_key'])) {
            $context['cache_key'] = $this->getCacheKey($format, $context);
        }

        $data = [
            '@rule' => $this->getAliasForRule($object)
        ];

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

        if (array_key_exists('sample', $data) && $data['sample'] === null) {
            unset($data['sample']);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if ($class != Rule::class) {
            throw new InvalidArgumentException(sprintf('Class must be equal to "%s".', Rule::class));
        }

        if (is_array($data)) {
            if (array_key_exists('@rule', $data)) {
                $class = $this->getClassNameFromAlias($data['@rule']);
                unset($data['@rule']);

                return $this->denormalizeRuleArray($data, $class, $format, $context);
            } else {
                foreach ($data as $key => $value) {
                    $data[$key] = $this->recursiveDenormalization($data[$key], $class, $format, $this->createChildContext($context, $key));
                }
            }

            return $data;
        }

        if (is_string($data) && $data != '') {
            return new StringEqualsRule($data, true);
        }

        return new EqualsRule($data);
    }

    private function denormalizeRuleArray($data, $class, $format = null, array $context = []): Rule
    {
        if (!isset($context['cache_key'])) {
            $context['cache_key'] = $this->getCacheKey($format, $context);
        }

        if (array_key_exists('value', $data) && is_array($data['value'])) {
            $data['value'] = $this->recursiveDenormalization($data['value'], Rule::class, $format, $this->createChildContext($context, 'value'));
        }

        $allowedAttributes = $this->getAllowedAttributes($class, $context, true);

        $reflectionClass = new \ReflectionClass($class);
        $object = $this->instantiateObject($data, $class, $context, $reflectionClass, $allowedAttributes, $format);

        foreach ($data as $attribute => $value) {
            if ($this->nameConverter) {
                $attribute = $this->nameConverter->denormalize($attribute);
            }

            if ((false !== $allowedAttributes && !in_array($attribute, $allowedAttributes)) || !$this->isAllowedAttribute($class, $attribute, $format, $context)) {
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

    private function recursiveDenormalization($data, $class, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof DenormalizerInterface) {
            throw new LogicException('Cannot denormalize data because the injected serializer is not a normalizer');
        }

        return $this->serializer->denormalize($data, $class, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function isAllowedAttribute($classOrObject, $attribute, $format = null, array $context = [])
    {
        if ($attribute == 'sample' && ($classOrObject instanceof EqualsRule || $classOrObject === EqualsRule::class || $classOrObject instanceof StringEqualsRule || $classOrObject === StringEqualsRule::class)) {
            return false;
        }

        return parent::isAllowedAttribute($classOrObject, $attribute, $format, $context);
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
            return md5($format.serialize($context));
        } catch (\Exception $exception) {
            // The context cannot be serialized, skip the cache
            return false;
        }
    }

    private function getAliasForRule(Rule $rule): string
    {
        $class = get_class($rule);

        return @array_flip($this->ruleAliases)[$class] ?: $class;
    }

    private function getClassNameFromAlias(string $alias): string
    {
        $class = @$this->ruleAliases[$alias] ?: $alias;

        if (!is_subclass_of($class, Rule::class)) {
            throw new InvalidArgumentException(sprintf('This "%s" is not a valid Rule or Rule\'s alias', $class, Rule::class));
        }

        return $class;
    }
}
