<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class AndRule extends AbstractRule
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
        if (!is_array($value)) {
            throw new Mismatches\TypeMismatch('array' , gettype($value));
        }

        foreach ($value as $item) {
            if (!$item instanceof Rule) {
                throw new Mismatches\TypeMismatch('Rule', gettype($value), 'Each item should be an instance of {{ expected }}');
            }
        }
    }

    public function assertMatch($test): void
    {
        $mismatches = [];

        /** @var Rule $item */
        foreach ($this->value as $item) {
            try {
                $item->assertMatch($test);
            } catch (Mismatches\Mismatch $e) {
                $mismatches[] = $e;
            }
        }

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches, 'One or more of the {{ count }} rules not matching the value');
        }
    }
}
