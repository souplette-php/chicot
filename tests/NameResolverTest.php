<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\Chicot\Internal\Contracts\ContainsName;
use Souplette\Chicot\Internal\NameResolver;

final class NameResolverTest extends TestCase
{
    #[DataProvider('resolveProvider')]
    public function testResolve(string $name, ?ContainsName $ns, string $expected): void
    {
        $resolver = new NameResolver($ns);
        Assert::assertSame($expected, $resolver->resolve($name));
    }

    public static function resolveProvider(): iterable
    {
        $yep = new class implements ContainsName {
            public function containsName(string $name): bool
            {
                return true;
            }
        };
        $nope = new class implements ContainsName {
            public function containsName(string $name): bool
            {
                return false;
            }
        };
        yield 'fully qualified' => [
            '\\Foo\\Bar', null, '\\Foo\\Bar',
        ];
        yield 'relative, no namespace' => [
            'Foo\\Bar', null, '\\Foo\\Bar',
        ];
        yield 'relative, in namespace' => [
            'Foo\\Bar', $yep, 'Bar',
        ];
        yield 'relative, not in namespace' => [
            'Foo\\Bar', $nope, '\\Foo\\Bar',
        ];
    }
}
