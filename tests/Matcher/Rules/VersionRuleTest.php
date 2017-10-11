<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class VersionRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $rule = new VersionRule('dev');

        $expected = [
            '@rule' => VersionRule::class,
            'value' => 'dev',
            'operator' => '=',
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        return [
            [false, 5],
            [false, 1.0],
            [false, ''],
            [false, true],
            [false, false],
            [false, null],
            [false, new class() {}],
            [false, new \stdClass()],
            [false, []],
            [true, 'dev'],
            [true, '1'],
            [true, '1.2'],
            [true, '1.2.3'],
            [true, '1.2.3-dev'],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(VersionRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            self::expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(VersionRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 'dev', '=', 'dev'],
            [true, '1', '<', 'dev'],
            [true, '1', '>', '1.2'],
            [true, '1.2', '>', '1.2.3'],
            [true, '1.2.3', '<', '1.2.3-dev'],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 'dev', '=', '0'],
            [false, '1', '>=', 'dev'],
            [false, '1', '<=', '1.2'],
            [false, '1.2', '<=', '1.2.3'],
            [false, '1.2.3', '>=', '1.2.3-dev'],
        ];
    }

    /**
     * @dataProvider matchesTrueProvider
     * @dataProvider matchesFalseProvider
     */
    public function testMatch(bool $shouldMatch, $ruleValue, $operator, $testValue)
    {
        if (!$shouldMatch) {
            self::expectException(Mismatches\ValueMismatch::class);
        }

        new VersionRule($ruleValue, $operator, $testValue);

        self::assertTrue(true, 'No exceptions should be thrown');
    }
}
