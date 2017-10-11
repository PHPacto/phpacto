<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class LowerRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $rule = new LowerOrEqualRule(5, 5);

        $expected = [
            '@rule' => LowerOrEqualRule::class,
            'value' => 5,
            'sample' => 5,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        return [
            [true, 100],
            [true, 10.0],
            [true, 'string'],
            [false, true],
            [false, false],
            [false, null],
            [false, []],
            [false, new class() {}],
            [false, new \stdClass()],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(LowerRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            self::expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(LowerRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testSampleIsMatchingRule()
    {
        $rule = self::getMockBuilder(LowerRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects(self::once())
            ->method('assertMatch')
            ->with(4)
            ->willReturn(true);

        $rule->__construct(5, 4);
    }

    /**
     * @depends testSampleIsMatchingRule
     */
    public function testExceptionIsTrhownIfSampleIsNotMatching()
    {
        self::expectException(Mismatches\ValueMismatch::class);
        self::expectExceptionMessage('should be lower than');

        new LowerRule(5, 5);
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 5, 4.9],
            [true, '90', '0'],
            [true, 'zzz', 'a'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 5.0, 5],
            [false, '', 'A'],
            [false, 'zzz', 'zzzz'],
        ];
    }

    /**
     * @depends testSampleIsMatchingRule
     * @dataProvider matchesTrueProvider
     * @dataProvider matchesFalseProvider
     */
    public function testMatch(bool $shouldMatch, $ruleValue, $testValue)
    {
        if (!$shouldMatch) {
            self::expectException(Mismatches\ValueMismatch::class);
            self::expectExceptionMessage('should be lower than');
        }

        new LowerRule($ruleValue, $testValue);

        self::assertTrue(true, 'No exceptions should be thrown');
    }
}
