<?php declare(strict_types=1);

require $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';

use Souplette\Chicot\Cli;

$app = new Cli();
exit($app->run($argv));
