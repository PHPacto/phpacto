<?php

namespace Bigfoot\PHPacto\Serializer;

use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Bigfoot\PHPacto\PactRequestInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class PactRequestNormalizerTest extends TestCase
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
        /** @var PactRequestNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(PactRequestNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization'])
            ->getMock();

        $pact = $this->createMock(PactRequestInterface::class);

        self::assertTrue($normalizer->supportsNormalization($pact, $format));
    }

    /**
     * @dataProvider normalizationFormatProvider
     */
    public function test_it_support_denormalization(?string $format)
    {
        /** @var PactRequestNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(PactRequestNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization'])
            ->getMock();

        self::assertTrue($normalizer->supportsDenormalization([], PactRequestInterface::class, $format));
    }

    public function test_serialize()
    {
        $serializer = SerializerFactory::getInstance();

        $request = $this->createMock(PactRequestInterface::class);

        $expected = [
            'method' => ['@rule' => get_class($request->getMethod()), 'value' => null],
            'uri' => ['@rule' => get_class($request->getUri()), 'value' => null],
        ];

        self::assertEquals($expected, $serializer->normalize($request, 'json'));
    }

    public function test_deserialize()
    {
        $serializer = SerializerFactory::getInstance();

        $data = [
            'method' => 'GET',
            'uri' => '/uri',
        ];

        /** @var PactRequestInterface $pact */
        $pact = $serializer->denormalize($data, PactRequestInterface::class, 'json');

        self::assertInstanceOf(PactRequestInterface::class, $pact);
        self::assertEquals('GET', $pact->getMethod()->getSample());
        self::assertEquals('/uri', $pact->getUri()->getSample());
    }
}
