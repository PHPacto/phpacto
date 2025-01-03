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

namespace PHPacto\Logger;

class StdoutLogger implements Logger
{
    /**
     * @var string[]
     */
    private $messages;

    /**
     * @param string[] $messages
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
    }

    public function __destruct()
    {
        $this->flush();
    }

    public function log(string $message): void
    {
        $this->messages[] = $message;
    }

    public function clear(): void
    {
        $this->messages = [];
    }

    public function flush(): void
    {
        file_put_contents('php://stdout', implode("\n", $this->messages));
    }
}
