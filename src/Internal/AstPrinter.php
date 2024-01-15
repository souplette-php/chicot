<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard as StdPrinter;

final class AstPrinter
{
    /**
     * @param Node[] $nodes
     * @return string
     */
    public static function printFile(array $nodes): string
    {
        $code = self::printer()->prettyPrintFile($nodes);
        return CodeFixer::fix($code);
    }

    /**
     * @param Node[] $nodes
     * @return string
     */
    public static function print(array $nodes): string
    {
        $code = self::printer()->prettyPrint($nodes);
        return CodeFixer::fix($code);
    }

    private static function printer(): StdPrinter
    {
        return new StdPrinter(['shortArraySyntax' => true]);
    }
}
