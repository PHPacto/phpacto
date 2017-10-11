<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;

interface Rule
{
    /**
     * Get rule value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Get rule sample
     *
     * @return mixed
     */
    public function getSample();

    /**
     * Match against a test value
     *
     * @throws Mismatch if not matching
     */
    public function assertMatch($test): void;
}
