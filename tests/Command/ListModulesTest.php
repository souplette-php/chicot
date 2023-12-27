<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests\Command;

use PHPUnit\Framework\TestCase;
use Souplette\Chicot\Command\ListModulesCommand;
use Symfony\Component\Console\Tester\CommandTester;

final class ListModulesTest extends TestCase
{
    public function testListExtensions(): void
    {
        $cmd = new ListModulesCommand();
        $tester = new CommandTester($cmd);
        $tester->execute([]);
        $tester->assertCommandIsSuccessful();
    }
}
