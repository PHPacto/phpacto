<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class ContainsRule extends AbstractRule
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
            throw new Mismatches\TypeMismatch('Rule', gettype($value), 'Value should be an instance of {{ expected }}');
        }
    }

    public function assertMatch($test): void
    {
        if (!is_array($test)) {
            throw new Mismatches\TypeMismatch('array', gettype($test));
        }

        foreach ($test as $item) {
            try {
                $this->value->assertMatch($item);

                // If at least one item match the value, its OK
                return;
            } catch (Mismatches\Mismatch $e) {
            }
        }

        throw new Mismatches\ValueMismatch('At least one item of array should match the rule', '', '');
    }
}
