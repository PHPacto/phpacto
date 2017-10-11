<?php

foreach (array(__DIR__ . '/../../../autoload.php', __DIR__ . '/../vendor/autoload.php') as $file) {
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
