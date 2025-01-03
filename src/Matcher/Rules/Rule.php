<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) Damian Długosz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 */

namespace PHPacto\Matcher\Rules;

use PHPacto\Matcher\Mismatches\Mismatch;

interface Rule
{
    /**
     * Return TRUE if rule has a custom sample that shloud be dumped into file
     */
    public function hasSample(): bool;

    /**
     * Get rule sample.
     *
     * @return mixed
     */
    public function getSample();

    /**
     * Match against a test value.
     *
     * @param mixed $test
     *
     * @throws Mismatch if not matching
     */
    public function assertMatch($test): void;
}
