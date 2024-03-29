<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests\Generator;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionFunction;
use Souplette\Chicot\Tests\GeneratorTestCase;
use function Souplette\Chicot\Mocks\by_ref_arg;
use function Souplette\Chicot\Mocks\constant_default_arg;
use function Souplette\Chicot\Mocks\default_arg;
use function Souplette\Chicot\Mocks\dnf_arg;
use function Souplette\Chicot\Mocks\intersection_arg;
use function Souplette\Chicot\Mocks\mixed_arg;
use function Souplette\Chicot\Mocks\nullable_arg;
use function Souplette\Chicot\Mocks\return_by_ref;
use function Souplette\Chicot\Mocks\union_arg;
use function Souplette\Chicot\Mocks\variadic_arg;
use function Souplette\Chicot\Mocks\with_doc_comment;

final class FunctionsTest extends GeneratorTestCase
{
    #[DataProvider('functionsProvider')]
    public function testFunctions(callable $fn, string $expected): void
    {
        $code = self::generateStubs('acme', functions: [
            new ReflectionFunction($fn),
        ]);
        self::assertCodeEq($expected, $code);
    }

    public static function functionsProvider(): iterable
    {
        yield 'mixed arg' => [
            mixed_arg(...),
            <<<'PHP'
            function mixed_arg(mixed $arg) : void {}
            PHP,
        ];
        yield 'nullable arg' => [
            nullable_arg(...),
            <<<'PHP'
            function nullable_arg(string|null $arg) : void {}
            PHP,
        ];
        yield 'variadic arg' => [
            variadic_arg(...),
            <<<'PHP'
            function variadic_arg(string ...$arg) : void {}
            PHP,
        ];
        yield 'default arg' => [
            default_arg(...),
            <<<'PHP'
            function default_arg(string $arg = 'foo') : void {}
            PHP,
        ];
        yield 'constant default arg' => [
            constant_default_arg(...),
            <<<'PHP'
            function constant_default_arg(int $a = \E_ERROR, int $b = \ReflectionClass::IS_FINAL) : void {}
            PHP,
        ];
        yield 'by ref arg' => [
            by_ref_arg(...),
            <<<'PHP'
            function by_ref_arg(array &$arg) : void {}
            PHP,
        ];
        yield 'return by reference' => [
            return_by_ref(...),
            <<<'PHP'
            function &return_by_ref() : int {}
            PHP,
        ];
        yield 'doc comment' => [
            with_doc_comment(...),
            <<<'PHP'
            /**
             * @return void
             */
            function with_doc_comment() : void {}
            PHP,
        ];
        yield 'union arg' => [
            union_arg(...),
            <<<'PHP'
            function union_arg(\ArrayAccess|array $arg) : void {}
            PHP,
        ];
        yield 'intersection arg' => [
            intersection_arg(...),
            <<<'PHP'
            function intersection_arg(\Traversable&\ArrayAccess $arg) : void {}
            PHP,
        ];
        yield 'dnf arg' => [
            dnf_arg(...),
            <<<'PHP'
            function dnf_arg((\Traversable&\ArrayAccess)|array $arg) : void {}
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
