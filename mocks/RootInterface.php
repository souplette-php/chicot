<?php declare(strict_types=1);

namespace Souplette\Chicot\Mocks;

/**
 * Das root.
 */
interface RootInterface
{
    /**
     * Everything
     */
    public const int EVERYTHING = 42;

    public function getEverything(): int;
}
