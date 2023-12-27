<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub\Stub;
use PHPUnit\Framework\TestCase;
use ReflectionExtension;
use Souplette\Chicot\StubsGenerator;

abstract class GeneratorTestCase extends TestCase
{
    protected static function assertCodeEq(string $expected, string $actual): void
    {
        $expected = "<?php\n\n{$expected}";
        Assert::assertSame(trim($expected), trim($actual));
    }

    protected static function generateStubs(
        string $name,
        array $constants = [],
        array $functions = [],
        array $classes = [],
    ): string {
        $ext = self::stubExtension($name, $constants, $functions, $classes);
        return StubsGenerator::generate($ext);
    }

    /**
     * @return ReflectionExtension&Stub
     * @throws Exception
     */
    protected static function stubExtension(
        string $name,
        array $constants = [],
        array $functions = [],
        array $classes = [],
    ): object {
        return self::createConfiguredStub(ReflectionExtension::class, [
            'getName' => $name,
            'getConstants' => $constants,
            'getFunctions' => $functions,
            'getClasses' => $classes,
        ]);
    }
}
