<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests\Generator;

use JsonSerializable;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Souplette\Chicot\Internal\Builder\FunctionLikeBuilder;
use Souplette\Chicot\Internal\AstPrinter;

final class TentativeReturnTypesTest extends TestCase
{
    public function testTentativeReturnType(): void
    {
        $rm = new ReflectionMethod(JsonSerializable::class, 'jsonSerialize');
        Assert::assertSame('mixed', (string)$rm->getTentativeReturnType());

        $expected = <<<'PHP'
        public function jsonSerialize() : mixed {}

        PHP;
        $builder = new FunctionLikeBuilder();
        $ast = $builder->buildMethod($rm);
        $result = AstPrinter::print([$ast]);
        Assert::assertSame($expected, $result);
    }
}
