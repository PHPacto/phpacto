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

function print_banner()
{
    $banner = file_get_contents(__DIR__ . '/../BANNER');

    echo str_replace("\n\n", PHP_EOL, $banner) . PHP_EOL;
}

foreach ([__DIR__ . '/../../../autoload.php', __DIR__ . '/../vendor/autoload.php'] as $autoloader) {
    if (file_exists($autoloader)) {
        require $autoloader;

        break;
    }
}

// Load environment
$_root_dir = getenv('PWD');
if ($_env_contracts_dir = getenv('CONTRACTS_DIR')) {
    if (\DIRECTORY_SEPARATOR !== $_env_contracts_dir[0]) {
        $_env_contracts_dir = $_root_dir . \DIRECTORY_SEPARATOR . $_env_contracts_dir;
    }
    define('CONTRACTS_DIR', realpath($_env_contracts_dir));
} else {
    define('CONTRACTS_DIR', $_root_dir);
}
