<?php

namespace Bigfoot\PHPacto;

interface PactInterface
{
    const VERSION = 'dev';

    public function getRequest(): PactRequestInterface;

    public function getResponse(): PactResponseInterface;

    public function getDescription(): string;

    public function getVersion(): string;
}
