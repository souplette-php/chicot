<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Souplette\Chicot\Mocks\AllTargetsAttr;
use Souplette\Chicot\Mocks\ClassAndMethodAttr;

final class ExtensionAttributesTest extends GeneratorTestCase
{
    #[DataProvider('attributesProvider')]
    public function testAttributes(string $name, string $expected): void
    {
        $code = self::generateStubs('acme', classes: [
            new ReflectionClass($name),
        ]);
        self::assertCodeEq($expected, $code);
    }

    public static function attributesProvider(): iterable
    {
        yield 'all targets' => [
            AllTargetsAttr::class,
            <<<'PHP'
            #[\Attribute(flags: \Attribute::TARGET_ALL | \Attribute::IS_REPEATABLE)]
            final class AllTargetsAttr {}
            PHP,
        ];
        yield 'class | method | property' => [
            ClassAndMethodAttr::class,
            <<<'PHP'
            #[\Attribute(flags: \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
            final class ClassAndMethodAttr {}
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
