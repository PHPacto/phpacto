<?php

/*
 * This file is part of PHPacto
 *
 * Copyright (c) 2017  Damian Długosz <bigfootdd@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2017  Damian Długosz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches\ValueMismatch;
use PHPUnit\Framework\TestCase;

final class RuleMockFactory extends TestCase
{
    public function empty(): Rule
    {
        return $this->createMock(Rule::class);
    }

    public function hasSample($sample): Rule
    {
        $rule = $this->empty();
        $rule->method('getSample')
            ->willReturn($sample);

        return $rule;
    }

    public function isMatching(): Rule
    {
        return $this->empty();
    }

    public function isNotMatching(string $mismatchType = ValueMismatch::class, array $arguments = ['', null, null]): Rule
    {
        $exception = new \ReflectionClass($mismatchType);

        $rule = $this->empty();
        $rule->method('assertMatch')
            ->willThrowException($exception->newInstanceArgs($arguments));

        return $rule;
    }

    public function hasSampleNotMatching($sample, string $mismatchType = ValueMismatch::class, array $arguments = ['', null, null]): Rule
    {
        $exception = new \ReflectionClass($mismatchType);

        $rule = $this->hasSample($sample);
        $rule->method('assertMatch')
            ->willThrowException($exception->newInstanceArgs($arguments));

        return $rule;
    }
}
