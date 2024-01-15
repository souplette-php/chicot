<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal\Contracts;

interface ContainsName
{
    public function containsName(string $name): bool;
}
