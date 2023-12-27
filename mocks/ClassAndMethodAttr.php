<?php declare(strict_types=1);

namespace Souplette\Chicot\Mocks;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD|Attribute::TARGET_PROPERTY)]
final class ClassAndMethodAttr
{
}
