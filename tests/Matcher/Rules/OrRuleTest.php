<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class OrRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $childRule = $this->rule->empty();
        $rule = new OrRule([$childRule]);

        $expected = [
            '@rule' => OrRule::class,
            'value' => [['@rule' => get_class($childRule), 'value' => null]],
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
        $rule = self::getMockBuilder(OrRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            self::expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(OrRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testMatch()
    {
        $mockOk = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockMismatch = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule = new OrRule([$mockMismatch, $mockOk]);

        $rule->assertMatch('No Mismatch is thrown');

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function testMismatch()
    {
        $mockMismatch = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockMismatch
            ->method('assertMatch')
            ->willThrowException(new Mismatches\ValueMismatch('A mismatch is expected', true, false));

        $rule = new OrRule([$mockMismatch, $mockMismatch]);

        self::expectException(Mismatches\MismatchCollection::class);

        $rule->assertMatch('A Mismatch should be thrown');
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
    }

    /**
     * @depends testMismatch
     */
    public function testGetSample()
    {
        $ruleA = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ruleA
            ->method('getSample')
            ->willReturn('A');

        $ruleB = self::getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ruleB
            ->method('getSample')
            ->willReturn('B');

        $rule = new OrRule([$ruleA, $ruleB]);

        self::assertContains($rule->getSample(), ['A', 'B']);
    }
}
