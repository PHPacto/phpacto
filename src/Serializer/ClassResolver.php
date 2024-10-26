<?php

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
