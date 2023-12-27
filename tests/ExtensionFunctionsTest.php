<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests;

use ReflectionFunction;
use function Souplette\Chicot\Mocks\nullable_arg;

final class ExtensionFunctionsTest extends GeneratorTestCase
{
    public function testNullableArg(): void
    {
        $code = self::generateStubs('acme', functions: [
            new ReflectionFunction(nullable_arg(...)),
        ]);
        $expected = <<<'PHP'
        namespace Souplette\Chicot\Mocks;

        function nullable_arg(string|null $arg) : void {}
        PHP;
        self::assertCodeEq($expected, $code);
    }
}
