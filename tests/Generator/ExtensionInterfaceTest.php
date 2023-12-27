<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests\Generator;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Souplette\Chicot\Mocks\ChildInterface;
use Souplette\Chicot\Mocks\RootInterface;
use Souplette\Chicot\Tests\GeneratorTestCase;

final class ExtensionInterfaceTest extends GeneratorTestCase
{
    #[DataProvider('generateInterfaceProvider')]
    public function testGenerateInterface(string $name, string $expected): void
    {
        $code = self::generateStubs('acme', classes: [
            new ReflectionClass($name),
        ]);
        self::assertCodeEq($expected, $code);
    }

    public static function generateInterfaceProvider(): iterable
    {
        yield 'interface' => [
            RootInterface::class,
            <<<'PHP'
            /**
             * Das root.
             */
            interface RootInterface
            {
                /**
                 * Everything
                 */
                public const int EVERYTHING = 42;
                public function getEverything() : int;
            }
            PHP,
        ];
        yield 'child interface' => [
            ChildInterface::class,
            <<<'PHP'
            interface ChildInterface
            {
                public const int ANYTHING = 0;
                public function getAnything(\Souplette\Chicot\Mocks\RootInterface $root) : int;
            }
            PHP,
        ];
    }

    protected static function assertCodeEq(string $expected, string $actual): void
    {
        $expected = <<<PHP
        namespace Souplette\Chicot\Mocks;

        {$expected}
        PHP;
        parent::assertCodeEq($expected, $actual);
    }
}
