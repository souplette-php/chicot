<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests\Generator;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionEnum;
use Souplette\Chicot\Mocks\TheNumbers;
use Souplette\Chicot\Mocks\TheStrings;
use Souplette\Chicot\Mocks\TheUnits;
use Souplette\Chicot\Tests\GeneratorTestCase;

final class EnumsTest extends GeneratorTestCase
{
    #[DataProvider('generateEnumProvider')]
    public function testGenerateEnum(string $name, string $expected): void
    {
        $code = self::generateStubs('acme', classes: [
            new ReflectionEnum($name),
        ]);
        self::assertCodeEq($expected, $code);
    }

    public static function generateEnumProvider(): iterable
    {
        yield 'int' => [
            TheNumbers::class,
            <<<'PHP'
            /**
             * The numbers.
             */
            enum TheNumbers : int
            {
                case TheBeginning = 0;
                case TheUniverseAndEverything = 42;
                case TheEnd = 666;
            }
            PHP,
        ];
        yield 'string' => [
            TheStrings::class,
            <<<'PHP'
            enum TheStrings : string
            {
                case Foo = 'foo';
                case Bar = 'bar';
                case Baz = 'baz';
            }
            PHP,
        ];
        yield 'unit' => [
            TheUnits::class,
            <<<'PHP'
            enum TheUnits
            {
                case KiB;
                case MiB;
                case GiB;
                public const int STEP = 1024;
                public function compute(int $n, self $unit) : int {}
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
