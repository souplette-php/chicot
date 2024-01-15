<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal\Builder;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Souplette\Chicot\Internal\NameResolver;

final readonly class TypeBuilder
{
    public function __construct(
        private NameResolver $nameResolver,
    ) {
    }

    public function build(ReflectionType $type): string
    {
        return match ($type::class) {
            ReflectionNamedType::class => $this->buildNamed($type),
            ReflectionIntersectionType::class => $this->buildIntersection($type),
            ReflectionUnionType::class => $this->buildUnion($type),
        };
    }

    private function buildNamed(ReflectionNamedType $type): string
    {
        $name = match ($type->isBuiltin()) {
            true => $type->getName(),
            false => match ($name = $type->getName()) {
                'self', 'static', 'parent' => $name,
                default => $this->nameResolver->resolve($name),
            },
        };
        if ($type->allowsNull() && !\in_array($name, ['mixed', 'null'])) {
            return "{$name}|null";
        }
        return $name;
    }

    private function buildIntersection(ReflectionIntersectionType $type): string
    {
        return implode('&', array_map($this->buildNamed(...), $type->getTypes()));
    }

    private function buildUnion(ReflectionUnionType $type): string
    {
        $types = [];
        foreach ($type->getTypes() as $type) {
            $s = $this->build($type);
            if ($type instanceof ReflectionIntersectionType) {
                $types[] = "({$s})";
            } else {
                $types[] = $s;
            }

        }
        return implode('|', $types);
    }
}
