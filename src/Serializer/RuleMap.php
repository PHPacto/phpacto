<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2018  Damian Długosz
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

namespace Bigfoot\PHPacto\Serializer;

use Bigfoot\PHPacto\Matcher\Rules;

final class RuleMap
{
    private $map = [
        'and' => Rules\AndRule::class,
        'boolean' => Rules\BooleanRule::class,
        'contains' => Rules\ContainsItemRule::class,
        'count' => Rules\CountItemsRule::class,
        'datetime' => Rules\DateTimeRule::class,
        'each' => Rules\EachItemRule::class,
        'equals' => Rules\EqualsRule::class,
        'exists' => Rules\ExistsRule::class,
        'greaterEqual' => Rules\GreaterOrEqualRule::class,
        'greater' => Rules\GreaterRule::class,
        'ifNotNull' => Rules\IfIsSetRule::class,
        'integer' => Rules\IntegerRule::class,
        'lowerEqual' => Rules\LowerOrEqualRule::class,
        'lower' => Rules\LowerRule::class,
        'number' => Rules\NumericRule::class,
        'or' => Rules\OrRule::class,
        'regex' => Rules\RegexpRule::class,
        'string' => Rules\StringRule::class,
        'stringBegins' => Rules\StringBeginsRule::class,
        'stringContains' => Rules\StringContainsRule::class,
        'stringEnds' => Rules\StringEndsRule::class,
        'stringEquals' => Rules\StringEqualsRule::class,
        'stringLength' => Rules\StringLengthRule::class,
        'uuid' => Rules\UuidRule::class,
        'version' => Rules\VersionRule::class,
    ];

    public function getRules(): array
    {
        return $this->map;
    }

    public function addRule(string $alias, string $className): void
    {
        $this->map[$alias] = $className;
    }

    public function getAlias(string $className): string
    {
        $inverse = \array_flip($this->map);

        if (!\array_key_exists($className, $inverse)) {
            throw new \RuntimeException(\sprintf('The class `%s` isn\'t registered', $className));
        }

        return $inverse[$className];
    }

    public function getClassName(string $alias): string
    {
        if (!\array_key_exists($alias, $this->map)) {
            throw new \RuntimeException(\sprintf('The alias `%s` isn\'t registered', $alias));
        }

        return $this->map[$alias];
    }
}
