<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal;

/**
 * @todo Replace this with https://github.com/pmjones/php-styler
 */
final class CodeFixer
{
    public static function fix(string $input): string
    {
        $output = self::fixFunctionBraces($input);
        $output = self::fixBlankLinesBetweenNamespaces($output);
        return self::ensureFinalNewLine($output);
    }

    private static function fixBlankLinesBetweenNamespaces(string $input): string
    {
        return preg_replace('/(?<!\n)\nnamespace/', "\n\nnamespace", $input);
    }

    private static function fixFunctionBraces(string $input): string
    {
        return preg_replace('/\s+\{\s+}/', ' {}', $input);
    }

    private static function ensureFinalNewLine(string $input): string
    {
        if (!str_ends_with("\n", $input)) {
            return $input . "\n";
        }
        return $input;
    }
}
