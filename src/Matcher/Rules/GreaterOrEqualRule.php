<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class GreaterOrEqualRule extends AbstractRule
{
    /**
     * @param $value
     * @param $sample
     */
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
        if (!(is_numeric($value) || is_string($value))) {
            throw new Mismatches\TypeMismatch(['number', 'string'] , gettype($value), 'Only {{ expected }} types are supported, {{ actual }} was provided');
        }
    }

    public function assertMatch($test): void
    {
        if (is_string($this->value) && !is_string($test)) {
            throw new Mismatches\TypeMismatch(gettype($this->value), gettype($test), 'Cannot compare different data types. A {{ expected }} was expected, but got {{ actual }} instead');
        }

        if (!($test >= $this->value)) {
            throw new Mismatches\ValueMismatch('Value {{ actual }} should be greater than or equal to {{ expected }}', $this->value, $test);
        }
    }
}
