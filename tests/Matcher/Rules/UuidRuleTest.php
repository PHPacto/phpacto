<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class UuidRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $rule = new UuidRule('00000000-0000-0000-0000-000000000000');

        $expected = [
            '@rule' => UuidRule::class,
            'value' => null,
            'sample' => '00000000-0000-0000-0000-000000000000'
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        // Uuid do not support values

        return [
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        throw new \Exception('Uuid do not support values');
    }

    public function testSampleIsMatchingRule()
    {
        $rule = self::getMockBuilder(RegexpRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects(self::once())
            ->method('assertMatch')
            ->with('content')
            ->willReturn(true);

        $rule->__construct('pattern', 'content');
    }

    /**
     * @depends testSampleIsMatchingRule
     */
    public function testExceptionIsTrhownIfSampleIsNotMatching()
    {
        self::expectException(Mismatches\ValueMismatch::class);

        new RegexpRule('.', '');
    }

    public function matchesTrueProvider()
    {
        return [
            [true, '^$', ''],
            [true, '^some (thing|else)$', 'some else'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, '0-9', 'F'],
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
            self::expectExceptionMessage('not matching the regex expression');
        }

        new RegexpRule($ruleValue, $testValue);

        self::assertTrue(true, 'No exceptions should be thrown');
    }
}
