<?php

namespace Bigfoot\PHPacto\Serializer;

use Bigfoot\PHPacto\PactInterface;
use Bigfoot\PHPacto\PactRequestInterface;
use Bigfoot\PHPacto\PactResponseInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class PactNormalizerTest extends TestCase
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
        /** @var PactNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(PactNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization'])
            ->getMock();

        $pact = $this->createMock(PactInterface::class);

        self::assertTrue($normalizer->supportsNormalization($pact, $format));
    }

    /**
     * @dataProvider normalizationFormatProvider
     */
    public function test_it_support_denormalization(?string $format)
    {
        /** @var PactNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(PactNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization'])
            ->getMock();

        self::assertTrue($normalizer->supportsDenormalization([], PactInterface::class, $format));
    }

    public function test_serialize()
    {
        $requestNormalizer = $this->getMockBuilder(PactRequestNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization'])
            ->getMock();

        $responseNormalizer = $this->getMockBuilder(PactResponseNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization'])
            ->getMock();

        $requestNormalizer
            ->expects(self::once())
            ->method('normalize')
            ->with(self::isInstanceOf(PactRequestInterface::class))
            ->willReturn(['Request']);

        $responseNormalizer
            ->expects(self::once())
            ->method('normalize')
            ->with(self::isInstanceOf(PactResponseInterface::class))
            ->willReturn(['Response']);

        $serializer = new Serializer(
            array(new PactNormalizer(), $requestNormalizer, $responseNormalizer),
            array(new JsonEncoder())
        );

        $pact = $this->createMock(PactInterface::class);

        $json = $serializer->serialize($pact, 'json');

        $expected = json_encode([
            'version' => '',
            'description' => '',
            'request' => ['Request'],
            'response' => ['Response'],
        ]);

        self::assertJsonStringEqualsJsonString($expected, $json);

        return $json;
    }

    /**
     * @depends test_serialize
     */
    public function test_deserialize(string $json)
    {
        $requestNormalizer = $this->getMockBuilder(PactRequestNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization'])
            ->getMock();

        $responseNormalizer = $this->getMockBuilder(PactResponseNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization'])
            ->getMock();

        $requestNormalizer
            ->expects(self::once())
            ->method('denormalize')
            ->with(self::equalTo(['Request']))
            ->willReturn($this->createMock(PactRequestInterface::class));

        $responseNormalizer
            ->expects(self::once())
            ->method('denormalize')
            ->with(self::equalTo(['Response']))
            ->willReturn($this->createMock(PactResponseInterface::class));

        $serializer = new Serializer(
            array(new PactNormalizer(), $requestNormalizer, $responseNormalizer),
            array(new JsonEncoder())
        );

        $pact = $serializer->deserialize($json, PactInterface::class, 'json');

        self::assertInstanceOf(PactInterface::class, $pact);
    }
}
