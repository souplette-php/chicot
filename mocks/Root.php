<?php declare(strict_types=1);

namespace Souplette\Chicot\Mocks;

/**
 * The root.
 */
class Root
{
    final public const int ZERO = 0;
    protected const int ONE = 1;
    private const int TWO = 2;

    public static string $static = 'static';

    /** @var int */
    public readonly int $public;
    protected string $protected = 'a';
    private string $private = 'b';

    public function publicMethod(): void
    {
    }
    protected function protectedMethod(): void
    {
    }
    private function privateMethod(): void
    {
    }
}
