<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class EachRule extends AbstractRule
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
            throw new Mismatches\TypeMismatch('Rule', gettype($value), '{{ actual }} should be an instance of {{ expected }} to match against each item of array');
        }
    }

    public function assertMatch($test): void
    {
        if (!is_array($test)) {
            throw new Mismatches\TypeMismatch('array', gettype($test));
        }

        $mismatches = [];

        foreach ($test as $item) {
            try {
                $this->value->assertMatch($item);
            } catch (Mismatches\Mismatch $e) {
                $mismatches[] = $e;
            }
        }

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches, 'One or more of the {{ count }} values not matching the rule');
        }
    }
}
