<?php declare(strict_types=1);

namespace Souplette\Chicot\Mocks;

use ArrayAccess;
use Traversable;

function nullable_arg(?string $arg): void {}
function variadic_arg(string ...$arg): void {}
function default_arg(string $arg = 'foo'): void {}
function by_ref_arg(array &$arg): void {}

/**
 * @return void
 */
function with_doc_comment(): void {}

function intersection_arg(Traversable&ArrayAccess $arg): void {}
function union_arg(array|ArrayAccess $arg): void {}
function dnf_arg(array|(Traversable&ArrayAccess) $arg): void {}
