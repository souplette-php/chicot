<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Souplette\Chicot\Mocks\AbstractMock;
use Souplette\Chicot\Mocks\Child;
use Souplette\Chicot\Mocks\GrandChild;
use Souplette\Chicot\Mocks\ReadOnlyMock;
use Souplette\Chicot\Mocks\Root;

final class ExtensionClassTest extends GeneratorTestCase
{
    #[DataProvider('generateClassProvider')]
    public function testGenerateClass(string $name, string $expected): void
    {
        $code = self::generateStubs('acme', classes: [
            new ReflectionClass($name),
        ]);
        self::assertCodeEq($expected, $code);
    }

    public static function generateClassProvider(): iterable
    {
        yield 'root mock' => [
            Root::class,
            <<<'PHP'
            /**
             * The root.
             */
            class Root
            {
                public final const int ZERO = 0;
                protected const int ONE = 1;
                public static string $static = 'static';
                /** @var int */
                public readonly int $public;
                protected string $protected = 'a';
                public function publicMethod() : void {}
                protected function protectedMethod() : void {}
            }
            PHP,
        ];
        yield 'child mock' => [
            Child::class,
            <<<'PHP'
            class Child extends \Souplette\Chicot\Mocks\Root {}
            PHP,
        ];
        yield 'final mock' => [
            GrandChild::class,
            <<<'PHP'
            final class GrandChild extends \Souplette\Chicot\Mocks\Child {}
            PHP,
        ];
        yield 'readonly mock' => [
            ReadOnlyMock::class,
            <<<'PHP'
            final readonly class ReadOnlyMock {}
            PHP,
        ];
        yield 'abstract mock' => [
            AbstractMock::class,
            <<<'PHP'
            abstract class AbstractMock
            {
                /**
                 * The end.
                 */
                public static final function finalMethod() : void {}
                public abstract function abstractMethod() : void;
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
