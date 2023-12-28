<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;

final class ReflectionUtils
{
    /**
     * Splits a fully-qualified name into a [$namespace, $shortName] pair.
     * @return array{string, string}
     */
    public static function splitName(string $name): array
    {
        $name = trim($name, '\\');
        if (false === $p = strrpos($name, '\\')) {
            return ['', $name];
        }
        $head = substr($name, 0, $p);
        $tail = substr($name, $p + 1);
        return [$head, $tail];
    }

    /**
     * @return iterable<ReflectionClass>
     */
    public static function ancestors(ReflectionClass $class): iterable
    {
        for ($ancestor = $class->getParentClass(); $ancestor; $ancestor = $ancestor->getParentClass()) {
            yield $ancestor;
        }
    }

    /**
     * @return ReflectionClassConstant[]
     */
    public static function getOwnConstants(ReflectionClass $class): array
    {
        return array_filter(
            $class->getReflectionConstants(),
            fn ($c) => $c->getDeclaringClass()->getName() === $class->getName(),
        );
    }

    /**
     * @return ReflectionProperty[]
     */
    public static function getOwnProperties(ReflectionClass $class): array
    {
        return array_filter(
            $class->getProperties(),
            fn ($p) => $p->getDeclaringClass()->getName() === $class->getName(),
        );
    }

    /**
     * @return ReflectionMethod[]
     */
    public static function getOwnMethods(ReflectionClass $class): array
    {
        return array_filter(
            $class->getMethods(),
            fn ($m) => $m->getDeclaringClass()->getName() === $class->getName(),
        );
    }

    /**
     * @return string[]
     */
    public static function getOwnInterfaceNames(ReflectionClass $class): array
    {
        $inherited = [];
        foreach (ReflectionUtils::ancestors($class) as $ancestor) {
            foreach ($ancestor->getInterfaceNames() as $name) {
                $inherited[$name] = true;
            }
        }
        return array_filter(
            $class->getInterfaceNames(),
            fn ($name) => !isset($inherited[$name])
        );
    }

    public static function isImplicitEnumMethod(ReflectionMethod $method): bool
    {
        return $method->isStatic() && match ($method->getName()) {
            "cases", "from", "tryFrom" => true,
            default => false,
        };
    }

    private const ATTR_TARGETS = [
        \Attribute::TARGET_CLASS => 'TARGET_CLASS',
        \Attribute::TARGET_FUNCTION => 'TARGET_FUNCTION',
        \Attribute::TARGET_METHOD => 'TARGET_METHOD',
        \Attribute::TARGET_PROPERTY => 'TARGET_PROPERTY',
        \Attribute::TARGET_CLASS_CONSTANT => 'TARGET_CLASS_CONSTANT',
        \Attribute::TARGET_PARAMETER => 'TARGET_PARAMETER',
    ];

    public static function getAttributeFlags(\Attribute $attr, BuilderFactory $factory): Expr
    {
        $class = '\\Attribute';
        $flags = $attr->flags;

        $exprs = [];
        if (($flags & \Attribute::TARGET_ALL) === \Attribute::TARGET_ALL) {
            $exprs[] = $factory->classConstFetch($class, 'TARGET_ALL');
        } else {
            foreach (self::ATTR_TARGETS as $value => $name) {
                if ($flags & $value) {
                    $exprs[] = $factory->classConstFetch($class, $name);
                }
            }
        }
        if ($flags & \Attribute::IS_REPEATABLE) {
            $exprs[] = $factory->classConstFetch($class, 'IS_REPEATABLE');
        }

        assert(\count($exprs) !== 0);
        return array_reduce(
            $exprs,
            static function (?Expr $acc, Expr $item) {
                if (!$acc) {
                    return $item;
                }
                if (!$acc instanceof BitwiseOr) {
                    return new BitwiseOr($acc, $item);
                }
                return new BitwiseOr(new BitwiseOr($acc->left, $acc->right), $item);
            }
        );
    }
}
