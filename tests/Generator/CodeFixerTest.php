<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests\Generator;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\Chicot\Internal\CodeFixer;

final class CodeFixerTest extends TestCase
{
    #[DataProvider('fixCodeProvider')]
    public function testFixCode(string $input, string $expected): void
    {
        Assert::assertSame($expected, CodeFixer::fix($input));
    }

    public static function fixCodeProvider(): iterable
    {
        yield 'adds final newline when missing' => [
            '<?php',
            "<?php\n",
        ];
        yield 'no redundant final newline' => [
            "<?php\n",
            "<?php\n",
        ];
        yield 'empty braces' => [
            "function()\n{\n}",
            "function() {}\n",
        ];
        yield 'non-empty braces' => [
            "function()\n{\nreturn 42;\n}",
            "function()\n{\nreturn 42;\n}\n",
        ];
        yield 'adds blank line before namespace declaration' => [
            "<?php\nnamespace foo;\nnamespace bar;",
            "<?php\n\nnamespace foo;\n\nnamespace bar;\n",
        ];
    }
}
