<?php

namespace Bigfoot\PHPacto\Logger;

interface Logger
{
    public function log(string $message): void;
}
