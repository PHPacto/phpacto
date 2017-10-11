<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class GreaterOrEqualRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $rule = new GreaterOrEqualRule(5, 6);

        $expected = [
            '@rule' => GreaterOrEqualRule::class,
            'value' => 5,
            'sample' => 6,
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
        $rule = self::getMockBuilder(GreaterOrEqualRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            self::expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(GreaterOrEqualRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
        $this->isWritable();
    }

    public function testSampleIsMatchingRule()
    {
        $rule = self::getMockBuilder(GreaterOrEqualRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects(self::once())
            ->method('assertMatch')
            ->with(5)
            ->willReturn(true);

        $rule->__construct(5, 5);
    }

    /**
     * @depends testSampleIsMatchingRule
     */
    public function testExceptionIsTrhownIfSampleIsNotMatching()
    {
        self::expectException(Mismatches\ValueMismatch::class);
        self::expectExceptionMessage('should be greater than or equal to');

        new GreaterOrEqualRule(5, 4);
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 5, 5.0],
            [true, '0', '90'],
            [true, 'a', 'zzz'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 5, 4.999],
            [false, 'A', ''],
            [false, 'zzzz', 'zzz'],
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
            self::expectExceptionMessage('should be greater than or equal to');
        }

        new GreaterOrEqualRule($ruleValue, $testValue);

        self::assertTrue(true, 'No exceptions should be thrown');
    }
}
