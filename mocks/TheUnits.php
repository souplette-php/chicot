<?php declare(strict_types=1);

namespace Souplette\Chicot\Mocks;

enum TheUnits
{
    case KiB;
    case MiB;
    case GiB;
    public const int STEP = 1024;

    public function compute(int $n, self $unit): int {
        return 42;
    }
}
