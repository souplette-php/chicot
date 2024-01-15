<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal;

final class NameResolver
{
    public function __construct(
        private ?ReflectionNamespace $namespace = null
    ) {
    }

    public function setCurrentNamespace(ReflectionNamespace $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function resolve(string $name): string
    {
        if (str_starts_with($name, '\\')) {
            // fully-qualified
            return $name;
        }
        if ($this->namespace?->containsName($name)) {
            [, $tail] = ReflectionUtils::splitName($name);
            return $tail;
        }
        return "\\{$name}";
    }

    /**
     * @param string[] $names
     * @return string[]
     */
    public function resolveMany(array $names): array
    {
        return array_map($this->resolve(...), $names);
    }
}
