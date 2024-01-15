<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests\Builder;

use PhpParser\BuilderFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use Souplette\Chicot\Internal\Builder\ClassConstantBuilder;
use Souplette\Chicot\Internal\Builder\FunctionLikeBuilder;
use Souplette\Chicot\Internal\Builder\PropertyBuilder;
use Souplette\Chicot\Internal\Builder\TypeBuilder;
use Souplette\Chicot\Internal\NameResolver;

final class PrivateItemsTest extends TestCase
{
    #[DataProvider('privateItemsCannotBeStubbedProvider')]
    public function testPrivateItemsCannotBeStubbed(callable $stub, string $message): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage($message);
        $stub();
    }

    public static function privateItemsCannotBeStubbedProvider(): iterable
    {
        $class = new class {
            private const int NOPE = 42;
            private int $nope;
            private function nope() {}
        };
        yield 'private constant' => [
            function() use($class) {
                $rcc = new ReflectionClassConstant($class, 'NOPE');
                $builder = new ClassConstantBuilder(new BuilderFactory(), new TypeBuilder(new NameResolver()));
                $builder->build($rcc);
            },
            'Private constants should not be stubbed.',
        ];
        yield 'private property' => [
            function() use($class) {
                $rp = new ReflectionProperty($class, 'nope');
                $builder = new PropertyBuilder(new BuilderFactory(), new TypeBuilder(new NameResolver()));
                $builder->build($rp);
            },
            'Private properties should not be stubbed.',
        ];
        yield 'private method' => [
            function() use($class) {
                $rm = new ReflectionMethod($class, 'nope');
                $builder = new FunctionLikeBuilder(new BuilderFactory(), new TypeBuilder(new NameResolver()));
                $builder->buildMethod($rm);
            },
            'Private methods should not be stubbed.',
        ];
    }
}
