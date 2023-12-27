<?php declare(strict_types=1);

require $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';

use Souplette\Chicot\Command\GenerateStubsCommand;
use Souplette\Chicot\Command\ListExtensionsCommand;
use Symfony\Component\Console\Application;

$app = new Application('chicot', '0.1.0');
$app->add(new ListExtensionsCommand('list-extensions'));
$app->add(new GenerateStubsCommand('stub'));
$app->run();
