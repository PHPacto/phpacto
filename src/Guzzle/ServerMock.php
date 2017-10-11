<?php

namespace Bigfoot\PHPacto\Guzzle;

use Bigfoot\PHPacto\PactInterface;
use GuzzleHttp\HandlerStack;

interface ServerMock
{
    public function handlePact(PactInterface $pact): void;
}
