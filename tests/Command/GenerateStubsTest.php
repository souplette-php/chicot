<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests\Command;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Chicot\Command\GenerateStubsCommand;
use Souplette\Chicot\Tests\TemporaryFileGuard;
use Symfony\Component\Console\Tester\CommandTester;

final class GenerateStubsTest extends TestCase
{
    public function testItPrintStubsToStdout(): void
    {
        $cmd = new GenerateStubsCommand('stub');
        $tester = new CommandTester($cmd);
        // json extension is a PHPUnit dependency, so should always be present in this environment.
        $tester->execute(['module' => 'json']);
        $tester->assertCommandIsSuccessful();
        $output = $tester->getDisplay();
        Assert::assertStringContainsString('function json_encode(', $output);
    }

    public function testItWritesStubsToFile(): void
    {
        $guard = new TemporaryFileGuard();
        $cmd = new GenerateStubsCommand('stub');
        $tester = new CommandTester($cmd);
        // json extension is a PHPUnit dependency, so should always be present in this environment.
        $tester->execute([
            'module' => 'json',
            'output-file' => $guard->path,
        ]);
        $tester->assertCommandIsSuccessful();

        Assert::assertFileExists($guard->path);
        Assert::assertStringContainsString('function json_encode(', $guard->getContents());
    }
}
