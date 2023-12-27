<?php declare(strict_types=1);

namespace Souplette\Chicot\Mocks;

use Attribute;

#[Attribute(flags: Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
final class AllTargetsAttr
{
}
