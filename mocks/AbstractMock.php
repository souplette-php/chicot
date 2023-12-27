<?php declare(strict_types=1);

namespace Souplette\Chicot\Mocks;

abstract class AbstractMock
{
    /**
     * The end.
     */
    final public static function finalMethod(): void {}
    abstract public function abstractMethod(): void;
}
