<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

class AbstractRuleTest extends RuleAbstractTest
{
    public function test_it_has_a_value_and_a_sample()
    {
        // Don't use RuleMockFactory because I want to test constructor arguments

        /** @var Rule $rule */
        $rule = $this->getMockBuilder(AbstractRule::class)
            ->setConstructorArgs(['value', 'sample'])
            ->setMethodsExcept(['getValue', 'getSample'])
            ->getMock();

        self::assertEquals('value', $rule->getValue());
        self::assertEquals('sample', $rule->getSample());
    }

    public function test_it_is_normalizable()
    {
        $rule = $this->rule->hasValueAndSample('value', 'sample');

        $expected = [
            '@rule' => get_class($rule),
            'value' => 'value',
            'sample' => 'sample',
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }
}
