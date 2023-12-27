<?php declare(strict_types=1);

require $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';

use Souplette\Chicot\Command\GenerateStubsCommand;
use Souplette\Chicot\Command\ListModulesCommand;
use Symfony\Component\Console\Application;

$app = new Application('chicot', '0.1.0');
$app->add(new ListModulesCommand('modules'));
$app->add(new GenerateStubsCommand('stub'));
$app->run();
