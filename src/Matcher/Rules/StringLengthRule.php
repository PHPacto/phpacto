<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class StringLengthRule extends AbstractRule
{
    public function __construct($value, $sample = null)
    {
        $this->assertSupport($value);

        parent::__construct($value, $sample);

        if (null !== $sample) {
            $this->assertMatch($sample);
        }
    }

    protected function assertSupport($value): void
    {
        if (!$value instanceof Rule) {
            throw new Mismatches\TypeMismatch('Rule', gettype($value), '{{ actual }} must be an instance of {{ expected }}');
        }
    }

    public function assertMatch($test): void
    {
        if (!is_string($test)) {
            throw new Mismatches\TypeMismatch('string', gettype($test));
        }

        try {
            $this->value->assertMatch(strlen($test));
        } catch (Mismatches\Mismatch $mismatch) {
            throw new Mismatches\ValueMismatch(
                'The lenght of string {{ actual }} should match the rule:' . "\n" .
                '    {{ expected }}',
                $mismatch->getMessage(),
                strlen($test)
            );
        }
    }
}
