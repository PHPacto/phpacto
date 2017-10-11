<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class EqualsRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $rule = new EqualsRule(5);

        $expected = 5;

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function test_it_is_normalizable_recursive()
    {
        $childRule = $this->rule->empty();
        $rule = new EqualsRule([
            $childRule,
            'key' => $childRule,
            'nested' => [
                'key' => $childRule,
            ]
        ]);

        $expected = [
            ['@rule' => get_class($childRule), 'value' => null],
            'key' => ['@rule' => get_class($childRule), 'value' => null],
            'nested' => [
                'key' => ['@rule' => get_class($childRule), 'value' => null],
            ],
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        $rule = self::getRuleMockFactory()->empty();

        return [
            [true, 100],
            [true, 1.0],
            [true, 'string'],
            [true, true],
            [true, false],
            [true, null],
            [true, []],
            [true, [[1]]],
            [true, [$rule]],
            [false, new class() {}],
            [false, new \stdClass()],
            [false, [new \stdClass()]],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(EqualsRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            self::expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(EqualsRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 5, 5],
            [true, 1, 1.0],
            [true, 'a', 'a'],
            [true, '', ''],
            [true, true, true],
            [true, false, false],
            [true, null, null],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 5, '5'],
            [false, '', 0],
            [false, 0, []],
            [false, 1, true],
            [false, 0, false],
            [false, false, null],
        ];
    }

    /**
     * @dataProvider matchesTrueProvider
     * @dataProvider matchesFalseProvider
     */
    public function testMatch(bool $shouldMatch, $ruleValue, $testValue)
    {
        $rule = new EqualsRule($ruleValue);

        if (!$shouldMatch) {
            self::expectException(Mismatches\ValueMismatch::class);
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown');
    }
}
