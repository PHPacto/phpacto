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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bigfoot\PHPacto\Controller;

use Http\Factory\Discovery\HttpFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (
            $request->getMethod() === 'OPTIONS'
            && $request->hasHeader('Access-Control-Request-Method')
        ) {
            $response = HttpFactory::responseFactory()->createResponse(204);
        } else {
            $response = $handler->handle($request);
        }

        return $response
            ->withAddedHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, LINK, UNLINK, DELETE, OPTIONS, HEAD')
            ->withAddedHeader('Access-Control-Allow-Credentials', 'True')
            ->withAddedHeader('Access-Control-Allow-Headers', '*')
            ->withAddedHeader('Access-Control-Allow-Origin', '*');
    }
}
