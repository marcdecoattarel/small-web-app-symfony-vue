<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Set test database configuration
if ($_SERVER['APP_ENV'] === 'test') {
    $_ENV['DATABASE_URL'] = 'sqlite:///:memory:';
    $_SERVER['DATABASE_URL'] = 'sqlite:///:memory:';
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
