<?php declare(strict_types=1);

namespace Souplette\Chicot;

use PhpParser\PrettyPrinter\Standard as AstPrinter;
use Psr\Log\LoggerInterface;
use ReflectionExtension;
use Souplette\Chicot\Internal\AstBuilder;
use Souplette\Chicot\Internal\CodeFixer;

final class StubsGenerator
{
    public static function generate(
        ReflectionExtension $ext,
        ?LoggerInterface $logger = null,
    ): string {
        $ast = AstBuilder::of($ext, $logger)->build();
        $printer = new AstPrinter(['shortArraySyntax' => true]);
        $code = $printer->prettyPrintFile($ast);
        return CodeFixer::fix($code);
    }
}
