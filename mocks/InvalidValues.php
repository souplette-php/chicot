<?php declare(strict_types=1);

// The STDERR constant is a stream resource, so we cannot generate stubs for it.
// We use it to assert that non-representable values are skipped
// and don't produce exceptions.

namespace Souplette\Chicot\Mocks;

final class InvalidValues
{
    public const INVALID = \STDERR;
    public static $invalid = \STDERR;
    public $invalidProp = \STDERR;

    public function invalid($invalid = \STDERR): void
    {
    }
}
