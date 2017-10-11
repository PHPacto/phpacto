<?php

namespace Bigfoot\PHPacto\Logger;

class StdoutLogger implements Logger
{
    public function log(string $message): void
    {
        file_put_contents('php://stdout', $message."\n");
    }
}
