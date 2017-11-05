<?php

/*
 * This file is part of PHPacto
 * Copyright (C) 2017  Damian Długosz
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

use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder as BaseJsonEncoder;

class JsonEncoder extends BaseJsonEncoder
{
    protected $defaultContext;

    public function __construct(JsonEncode $encodingImpl = null, JsonDecode $decodingImpl = null, array $defaultContext = [])
    {
        parent::__construct($encodingImpl, $decodingImpl);

        $this->defaultContext = $defaultContext;
    }

    public function encode($data, $format, array $context = [])
    {
        return parent::encode($data, $format, array_merge($this->defaultContext, $context));
    }

    public function decode($data, $format, array $context = [])
    {
        return parent::decode($data, $format, array_merge($this->defaultContext, $context));
    }
}
