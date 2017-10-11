<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class NotEqualsRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $rule = new NotEqualsRule(5, 6);

        $expected = [
            '@rule' => NotEqualsRule::class,
            'value' => 5,
            'sample' => 6
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        return [
            [true, 100],
            [true, 1.0],
            [true, 'string'],
            [true, true],
            [true, false],
            [true, null],
            [true, []],
            [true, [[1]]],
            [false, new class() {}],
            [false, new \stdClass()],
            [false, [[new \stdClass()]]],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(NotEqualsRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            self::expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(NotEqualsRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testSampleIsMatchingRule()
    {
        $rule = self::getMockBuilder(NotEqualsRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects(self::once())
            ->method('assertMatch')
            ->with(6)
            ->willReturn(true);

        $rule->__construct(5, 6);
    }

    /**
     * @depends testSampleIsMatchingRule
     */
    public function testExceptionIsTrhownIfSampleIsNotMatching()
    {
        self::expectException(Mismatches\ValueMismatch::class);
        self::expectExceptionMessage('should be different');

        new NotEqualsRule(5, 5);
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 5, 5],
            [false, 1, 1.0],
            [false, 'a', 'a'],
            [false, '', ''],
            [false, true, true],
            [false, false, false],
            [false, null, null],
        ];
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 5, '5'],
            [true, '', 0],
            [true, 1, true],
            [true, 0, false],
            [true, null, -1],
        ];
    }

    /**
     * @depends testSampleIsMatchingRule
     * @dataProvider matchesTrueProvider
     * @dataProvider matchesFalseProvider
     */
    public function testMatch(bool $shouldMatch, $ruleValue, $testValue)
    {
        $this->markTestIncomplete('check later');

        if (!$shouldMatch) {
            self::expectException(Mismatches\ValueMismatch::class);
        }

        new NotEqualsRule($ruleValue, $testValue);

        self::assertTrue(true, 'No exceptions should be thrown');
    }
}
