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

namespace PHPacto\Serializer;

use PHPacto\Pact;
use PHPacto\PactInterface;
use PHPacto\PactRequest;
use PHPacto\PactRequestInterface;
use PHPacto\PactResponse;
use PHPacto\PactResponseInterface;

class ClassResolver
{
    public function __invoke(object $type): string
    {
        return match(get_class($type)) {
            PactInterface::class => Pact::class,
            PactRequestInterface::class => PactRequest::class,
            PactResponseInterface::class => PactResponse::class,
            // Aggiungi qui altre mappature interfaccia => classe
            default => get_class($type)
        };
    }
}
