<?php declare(strict_types=1);

namespace Souplette\Chicot\Mocks;

interface ChildInterface
{
    public const int ANYTHING = 0;

    public function getAnything(RootInterface $root): int;
}
