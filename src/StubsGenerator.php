<?php declare(strict_types=1);

namespace Souplette\Chicot;

use Psr\Log\LoggerInterface;
use ReflectionExtension;
use Souplette\Chicot\Internal\AstPrinter;
use Souplette\Chicot\Internal\ExtensionBuilder;

final class StubsGenerator
{
    public static function generate(
        ReflectionExtension $ext,
        ?LoggerInterface $logger = null,
    ): string {
        $builder = new ExtensionBuilder($logger);
        return AstPrinter::printFile($builder->build($ext));
    }
}
