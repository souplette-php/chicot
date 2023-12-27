<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests;

use PHPUnit\Framework\Attributes\DataProvider;

final class ExtensionConstantsTest extends GeneratorTestCase
{
    #[DataProvider('generateConstantsProvider')]
    public function testGenerateConstants(array $constants, string $expected): void
    {
        $stubs = self::generateStubs('acme', constants: $constants);
        self::assertCodeEq($expected, $stubs);
    }

    public static function generateConstantsProvider(): iterable
    {
        yield 'single namespace' => [
            [
                'Acme\\EVERYTHING' => 42,
            ],
            <<<'PHP'
            namespace Acme;

            const EVERYTHING = 42;
            PHP,
        ];
        yield 'several namespace' => [
            [
                'Acme\\FOO' => 'foo',
                'Other\\FOO' => 'bar',
            ],
            <<<'PHP'
            namespace Acme;

            const FOO = 'foo';

            namespace Other;

            const FOO = 'bar';
            PHP,
        ];
    }
}
