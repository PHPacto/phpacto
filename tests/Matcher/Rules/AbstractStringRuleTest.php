<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

class AbstractStringRuleTest extends RuleAbstractTest
{
    public function test_it_is_not_case_sensitive_by_default()
    {
        $rule = $this->getMockBuilder(AbstractStringRule::class)
            ->setConstructorArgs(['value', 'sample'])
            ->setMethodsExcept(['isCaseSensitive'])
            ->getMock();

        self::assertFalse($rule->isCaseSensitive());
    }

    public function test_it_is_case_sensitive()
    {
        $rule = $this->getMockBuilder(AbstractStringRule::class)
            ->setConstructorArgs(['value', 'sample', true])
            ->setMethodsExcept(['isCaseSensitive'])
            ->getMock();

        self::assertTrue($rule->isCaseSensitive());
    }

    public function test_it_is_normalizable()
    {
        $rule = $this->getMockBuilder(AbstractStringRule::class)
            ->setConstructorArgs(['value', 'sample', true])
            ->setMethods(null)
            ->getMock();

        $expected = [
            '@rule' => get_class($rule),
            'value' => 'value',
            'sample' => 'sample',
            'caseSensitive' => true,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }
}
