<?php declare(strict_types=1);

namespace Souplette\Chicot;

use PhpParser\PrettyPrinter\Standard as AstPrinter;
use Psr\Log\LoggerInterface;
use ReflectionExtension;
use Souplette\Chicot\Internal\ExtensionBuilder;
use Souplette\Chicot\Internal\CodeFixer;

final class StubsGenerator
{
    public static function generate(
        ReflectionExtension $ext,
        ?LoggerInterface $logger = null,
    ): string {
        $builder = new ExtensionBuilder($logger);
        $printer = new AstPrinter(['shortArraySyntax' => true]);
        $code = $printer->prettyPrintFile($builder->build($ext));
        return CodeFixer::fix($code);
    }
}
