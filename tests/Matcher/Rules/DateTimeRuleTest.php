<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class DateTimeRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $this->markTestIncomplete();

        $rule = new DateTimeRule('*', '0');

        $expected = [
            '@rule' => DateTimeRule::class,
            'value' => '*',
            'sample' => '0'
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        return [
            [false, 5],
            [false, 1.0],
            [true, 'string'],
            [true, '2017-09-23'],
            [false, true],
            [false, false],
            [false, null],
            [false, new class() {}],
            [false, new \stdClass()],
            [false, []],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(DateTimeRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            self::expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(DateTimeRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testSampleIsMatchingRule()
    {
        $rule = self::getMockBuilder(DateTimeRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects(self::once())
            ->method('assertMatch')
            ->with('23-03-1990')
            ->willReturn(true);

        $rule->__construct('d-m-Y', '23-03-1990');
    }

    /**
     * @depends testSampleIsMatchingRule
     */
    public function testExceptionIsTrhownIfSampleIsNotMatching()
    {
        self::expectException(Mismatches\ValueMismatch::class);

        new DateTimeRule('d-m-Y', '1990');
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 'Y-m-d', '2017-10-31'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 'test', ''],
            [false, 'Y-m-d', '2017.10.31'],
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
        }

        new DateTimeRule($ruleValue, $testValue);

        self::assertTrue(true, 'No exceptions should be thrown');
    }
}
