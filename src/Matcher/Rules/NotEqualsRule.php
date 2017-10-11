<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class NotEqualsRule extends AbstractRule
{
    public function __construct($value, $sample = null)
    {
        $this->assertSupport($value);
        $this->assertSupport($sample);

        parent::__construct($value, $sample);

        if (null !== $sample) {
            $this->assertMatch($sample);
        }
    }

    protected function assertSupport($value): void
    {
        if (is_object($value)) {
            throw new Mismatches\TypeMismatch(['null', 'boolean', 'number', 'string', 'array'] , gettype($value), 'Objects are not supported');
        } elseif (is_array($value)) {
            array_walk($value, function ($value) {
                $this->assertSupport($value);
            });
        }
    }

    public function assertMatch($test): void
    {
        $types = [
            gettype($this->value),
            gettype($test)
        ];

        if ($types != ['integer', 'double'] && $types != ['double', 'integer']) {
            if ($types[0] != $types[1]) {
                throw new Mismatches\TypeMismatch($types[0], $types[1], 'Cannot compare different data types. A {{ expected }} was expected, but got {{ actual }} instead');
            }
        }

        if ($this->value == $test) {
            throw new Mismatches\ValueMismatch('Value {{ actual }} should be different than {{ expected }}', $this->value, $test);
        }
    }
}
