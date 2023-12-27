<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests\Command;

use PHPUnit\Framework\TestCase;
use Souplette\Chicot\Command\ListExtensionsCommand;
use Symfony\Component\Console\Tester\CommandTester;

final class ListExtensionsTest extends TestCase
{
    public function testListExtensions(): void
    {
        $cmd = new ListExtensionsCommand();
        $tester = new CommandTester($cmd);
        $tester->execute([]);
        $tester->assertCommandIsSuccessful();
    }
}
