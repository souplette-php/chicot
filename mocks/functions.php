<?php declare(strict_types=1);

namespace Souplette\Chicot\Mocks;

use ArrayAccess;
use Traversable;

function mixed_arg(mixed $arg): void {}
function nullable_arg(?string $arg): void {}
function variadic_arg(string ...$arg): void {}
function default_arg(string $arg = 'foo'): void {}
function constant_default_arg(int $a = \E_ERROR, int $b = \ReflectionClass::IS_FINAL): void {}
function by_ref_arg(array &$arg): void {}
function &return_by_ref(): int {}

/**
 * @return void
 */
function with_doc_comment(): void {}

function intersection_arg(Traversable&ArrayAccess $arg): void {}
function union_arg(array|ArrayAccess $arg): void {}
function dnf_arg(array|(Traversable&ArrayAccess) $arg): void {}
