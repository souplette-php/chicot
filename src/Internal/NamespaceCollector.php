<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal;

use ReflectionExtension;

/**
 * Collects extension items into a namespaced-keyed array.
 */
final class NamespaceCollector
{
    public static function collect(ReflectionExtension $extension): array
    {
        $map = [];
        foreach ($extension->getConstants() as $key => $value) {
            [$ns, $name] = ReflectionUtils::splitName($key);
            $k = strtolower($ns);
            $map[$k] ??= new ReflectionNamespace($ns);
            $map[$k]->addConstant($name, $value);
        }
        foreach ($extension->getFunctions() as $fn) {
            $ns = $fn->getNamespaceName();
            $k = strtolower($ns);
            $map[$k] ??= new ReflectionNamespace($ns);
            $map[$k]->addFunction($fn);
        }
        foreach ($extension->getClasses() as $class) {
            $ns = $class->getNamespaceName();
            $k = strtolower($ns);
            $map[$k] ??= new ReflectionNamespace($ns);
            $map[$k]->addClass($class);
        }
        ksort($map);
        return $map;
    }

    private static function capitalize(string $ns): string
    {
        $parts = array_map(ucfirst(...), explode("\\", $ns));
        return implode("\\", $parts);
    }
}
