<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use PHPUnit\Framework\Test;

interface RuleTestCase extends Test
{
    /**
     * Each Rule should be normalizable
     */
    public function test_it_is_normalizable();

    /**
     * Each Rule should be denormalizable
     */
//    public function test_it_is_denormalizable();
}
