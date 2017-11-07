<?php

/*
 * This file is part of PHPacto
 *
 * Copyright (c) 2017  Damian Długosz
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

foreach ([__DIR__.'/../../../autoload.php', __DIR__.'/../vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        require $file;

        break;
    }
}

// Load environment
define('ROOT', getenv('PWD'));

if (file_exists(ROOT.'/.env')) {
    (new Dotenv\Dotenv(ROOT))->load();
}

define('CONTRACTS_DIR', ROOT.'/'.getenv('CONTRACTS_DIR'));
