<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests\Namespace;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use Souplette\Chicot\Internal\ReflectionNamespace;

#[CoversClass(ReflectionNamespace::class)]
final class ReflectionNamespaceTest extends TestCase
{
    public function testGetName(): void
    {
        $ns = new ReflectionNamespace('Acme');
        Assert::assertSame('Acme', $ns->getName());
    }

    public function testAddConstant(): void
    {
        $ns = new ReflectionNamespace('Acme');
        $ns->addConstant('Acme\\FOOBAR', 'BAZ');
        Assert::assertTrue($ns->containsName('Acme\\FOOBAR'));

        $consts = [
            'Acme\\FOOBAR' => 'BAZ',
        ];
        Assert::assertSame($consts, $ns->getConstants());
    }

    public function testAddFunction(): void
    {
        $ns = new ReflectionNamespace('Acme');
        $fn = $this->createConfiguredStub(ReflectionFunction::class, [
            'getName' => 'Acme\\my_function',
        ]);
        $ns->addFunction($fn);
        Assert::assertTrue($ns->containsName('Acme\\my_function'));

        $funcs = [
            'Acme\\my_function' => $fn,
        ];
        Assert::assertSame($funcs, $ns->getFunctions());
    }

    public function testAddClass(): void
    {
        $ns = new ReflectionNamespace('Acme');
        $class = $this->createConfiguredStub(ReflectionClass::class, [
            'getName' => 'Acme\\MyClass',
        ]);
        $ns->addClass($class);
        Assert::assertTrue($ns->containsName('Acme\\MyClass'));

        $classes = [
            'Acme\\MyClass' => $class,
        ];
        Assert::assertSame($classes, $ns->getClasses());
    }
}
