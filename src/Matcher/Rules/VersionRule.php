<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class VersionRule extends AbstractRule
{
    /**
     * @var string
     */
    protected $operator;

    public function __construct($value, $operator = '=', $sample = null)
    {
        $this->assertSupport($value);
        $this->assertSupportOperator($operator);

        parent::__construct($value, $sample);

        $this->operator = $operator;

        if (null !== $sample) {
            $this->assertMatch($sample);
        }
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    protected function assertSupport($value): void
    {
        if (!is_string($value)) {
            throw new Mismatches\TypeMismatch('string' , gettype($value));
        }

        if ($value == '') {
            throw new Mismatches\TypeMismatch('string' , 'empty');
        }
    }

    protected function assertSupportOperator($operator): void
    {
        $allowedOperators = ['<', '<=', '=', '>=', '>', ];

        if (!in_array($operator, $allowedOperators)) {
            throw new Mismatches\ValueMismatch('Only one operator of {{ expected }} is supported, but given {{ actual }}', $allowedOperators, $operator);
        }
    }

    public function assertMatch($test): void
    {
        $this->assertSupport($test);

        if (!version_compare($test, $this->value, $this->operator)) {
            switch ($this->operator) {
                case '<': $operatorString = 'lower than'; break;
                case '<=': $operatorString = 'lower than or equal to'; break;
                case '=': $operatorString = 'equal to'; break;
                case '>=': $operatorString = 'greater than or equal to'; break;
                case '>': $operatorString = 'greater than'; break;
                default: $operatorString = '';
            }

            throw new Mismatches\ValueMismatch('Version {{ actual }} should be '.$operatorString.' {{ expected }}', $this->value, $test);
        }
    }
}
