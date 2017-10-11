<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class OrRule extends AbstractRule
{
    public function __construct($value)
    {
        $this->assertSupport($value);

        parent::__construct($value);
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

                // If at least one item match the value, its OK
                return;
            } catch (Mismatches\Mismatch $e) {
                $mismatches[] = $e;
            }
        }

        if (count($mismatches) == count($this->value)) {
            throw new Mismatches\MismatchCollection($mismatches, 'None of the {{ count }} rules is matching');
        }
    }

    public function getSample()
    {
        if (count($this->value)) {
            /** @var Rule $rule */
            $rule = $this->value[array_rand($this->value)];

            return $rule->getSample();
        }
    }
}
