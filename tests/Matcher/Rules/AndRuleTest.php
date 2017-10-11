<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class AndRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $childRule = $this->rule->empty();
        $rule = new AndRule([$childRule], 'sample');

        $expected = [
            '@rule' => AndRule::class,
            'value' => [['@rule' => get_class($childRule), 'value' => null]],
            'sample' => 'sample',
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        $rule = self::getRuleMockFactory()->empty();

        return [
            [true, []],
            [false, 100],
            [false, 1.0],
            [false, 'string'],
            [false, true],
            [false, false],
            [false, null],
            [false, new class() {}],
            [false, new \stdClass()],
            [false, $rule],
            [false, [[]]],
            [false, [100]],
            [false, [1.0]],
            [false, ['string']],
            [false, [true]],
            [false, [false]],
            [false, [null]],
            [false, [new class() {}]],
            [false, [new \stdClass()]],
            [true, [$rule]],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(AndRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            self::expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(AndRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testMatch()
    {
        $mock = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        new AndRule([$mock], 'No Mismatch is thrown');

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testMismatch()
    {
        $mockOk = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockMismatch = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockMismatch
            ->method('assertMatch')
            ->willThrowException(new Mismatches\ValueMismatch('A mismatch is expected', true, false));

        self::expectException(Mismatches\MismatchCollection::class);
        self::expectExceptionMessage('rules not matching the value');

        new AndRule([$mockOk, $mockMismatch, $mockOk, $mockMismatch, $mockOk], 'A Mismatch should be thrown');
    }

    /**
     * @depends testMismatch
     */
    public function testMismatchCount()
    {
        try {
            $this->testMismatch();
        } catch (Mismatches\MismatchCollection $e) {
            self::assertEquals(2, count($e));

            throw $e;
        }

        self::assertFalse(true, 'This test should end in the catch');
    }
}
