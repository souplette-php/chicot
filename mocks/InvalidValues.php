<?php declare(strict_types=1);

// The STDERR constant is a stream resource, so we can't stub it
// when used as a class constant or property default.
// Function parameters whose default is a constant
// can however retrieve the constant name,
// so we use an object expression.

namespace Souplette\Chicot\Mocks;

final class InvalidValues
{
    public const INVALID = \STDERR;
    public static $invalid = \STDERR;
    public $invalidProp = \STDERR;

    public function invalid($invalid = new \stdClass()): void
    {
    }
}
