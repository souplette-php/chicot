<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal;

use Attribute;
use PhpParser\Builder;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionExtension;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

final class AstBuilder
{
    private readonly BuilderFactory $builderFactory;
    private ReflectionNamespace $currentNamespace;

    private function __construct(
        private readonly ReflectionExtension $extension,
        private readonly ?LoggerInterface $logger = null,
    ) {
        $this->builderFactory = new BuilderFactory();
    }

    public static function of(ReflectionExtension $extension, ?LoggerInterface $logger = null): self
    {
        return new self($extension, $logger);
    }

    /**
     * @return Stmt\Namespace_[]
     */
    public function build(): array
    {
        $namespaces = NamespaceCollector::collect($this->extension);
        $nodes = [];
        foreach ($namespaces as $namespace) {
            $nodes[] = $this->buildNamespace($namespace);
        }
        return $nodes;
    }

    private function buildNamespace(ReflectionNamespace $namespace): Stmt\Namespace_
    {
        $this->currentNamespace = $namespace;
        $builder = $this->builderFactory->namespace(match ($name = $namespace->name) {
            '' => null,
            default => $name,
        });
        foreach ($namespace->getConstants() as $name => $value) {
            if (!self::canStubValue($value)) {
                $this->logger?->warning("Cannot stub non-scalar value of type `{type}` for constant: `{name}`", [
                    'type' => get_debug_type($value),
                    'name' => $name,
                ]);
                continue;
            }
            $builder->addStmt($this->buildConstant($name, $value));
        }
        foreach ($namespace->getFunctions() as $function) {
            $builder->addStmt($this->buildFunction($function));
        }
        foreach ($namespace->getClasses() as $class) {
            if ($class->isInterface()) {
                $builder->addStmt($this->buildInterface($class));
            } elseif ($class instanceof ReflectionEnum) {
                $builder->addStmt($this->buildEnum($class));
            } else {
                $builder->addStmt($this->buildClass($class));
            }
        }
        return $builder->getNode();
    }

    private function buildConstant(string $name, mixed $value): Stmt\Const_
    {
        return new Stmt\Const_([
            new Node\Const_($name, $this->builderFactory->val($value)),
        ]);
    }

    private function buildFunction(ReflectionFunction $function): Stmt\Function_
    {
        $builder = $this->builderFactory->function($function->getShortName());
        if ($doc = $function->getDocComment()) {
            $builder->setDocComment($doc);
        }
        if ($function->hasReturnType()) {
            $builder->setReturnType($this->buildType($function->getReturnType()));
        }
        foreach ($function->getParameters() as $parameter) {
            $builder->addParam($this->buildParameter($parameter));
        }
        return $builder->getNode();
    }

    private function buildInterface(ReflectionClass $interface): Stmt\Interface_
    {
        $builder = $this->builderFactory->interface($interface->getShortName());
        if ($doc = $interface->getDocComment()) {
            $builder->setDocComment($doc);
        }
        $builder->extend(...$this->resolveNames(ReflectionUtils::getOwnInterfaceNames($interface)));
        foreach ($interface->getConstants() as $name => $value) {
            $constant = new ReflectionClassConstant($interface->getName(), $name);
            $builder->addStmt($this->buildClassConst($constant));
        }
        foreach (ReflectionUtils::getOwnMethods($interface) as $method) {
            $builder->addStmt($this->buildMethod($method));
        }
        return $builder->getNode();
    }

    private function buildClass(ReflectionClass $class): Stmt\Class_
    {
        $builder = $this->builderFactory->class($class->getShortName());
        if ($doc = $class->getDocComment()) {
            $builder->setDocComment($doc);
        }
        if ($class->isFinal()) {
            $builder->makeFinal();
        } elseif ($class->isAbstract()) {
            $builder->makeAbstract();
        }
        if ($class->isReadOnly()) {
            $builder->makeReadonly();
        }
        $this->maybeMarkAsAttribute($class, $builder);
        if ($parent = $class->getParentClass()) {
            $builder->extend($this->resolveName($parent->getName()));
        }
        $builder->implement(...$this->resolveNames(ReflectionUtils::getOwnInterfaceNames($class)));
        foreach (ReflectionUtils::getOwnConstants($class) as $constant) {
            if ($constant->isPrivate()) {
                continue;
            }
            if (!self::canStubValue($value = $constant->getValue())) {
                $this->logger?->warning("Cannot stub non-scalar value of type `{type}` for class constant: `{name}`", [
                    'type' => get_debug_type($value),
                    'name' => sprintf('%s::%s', $class->getName(), $constant->getName()),
                ]);
                continue;
            }
            $builder->addStmt($this->buildClassConst($constant));
        }
        foreach (ReflectionUtils::getOwnProperties($class) as $property) {
            if ($property->isPrivate()) {
                continue;
            }
            $builder->addStmt($this->buildProperty($property));
        }
        foreach (ReflectionUtils::getOwnMethods($class) as $method) {
            if ($method->isPrivate()) {
                continue;
            }
            $builder->addStmt($this->buildMethod($method));
        }
        return $builder->getNode();
    }

    private function buildEnum(ReflectionEnum $enum): Stmt\Enum_
    {
        $builder = $this->builderFactory->enum($enum->getShortName());
        if ($doc = $enum->getDocComment()) {
            $builder->setDocComment($doc);
        }
        if ($type = $enum->getBackingType()) {
            $builder->setScalarType($type->getName());
        }
        foreach ($enum->getCases() as $case) {
            $cb = $this->builderFactory->enumCase($case->getName());
            if ($case instanceof ReflectionEnumBackedCase) {
                $cb->setValue($case->getBackingValue());
            }
            $builder->addStmt($cb->getNode());
        }
        foreach ($enum->getConstants() as $name => $value) {
            $constant = new ReflectionClassConstant($enum->getName(), $name);
            if ($constant->isEnumCase()) {
                continue;
            }
            $builder->addStmt($this->buildClassConst($constant));
        }
        foreach (ReflectionUtils::getOwnMethods($enum) as $method) {
            if (ReflectionUtils::isImplicitEnumMethod($method)) {
                continue;
            }
            $builder->addStmt($this->buildMethod($method));
        }
        return $builder->getNode();
    }

    private function buildClassConst(ReflectionClassConstant $constant): Stmt\ClassConst
    {
        $builder = $this->builderFactory->classConst($constant->getName(), $constant->getValue());
        if ($doc = $constant->getDocComment()) {
            $builder->setDocComment($doc);
        }
        if ($constant->hasType()) {
            $builder->setType($this->buildType($constant->getType()));
        }
        assert(!$constant->isPrivate());
        if ($constant->isProtected()) {
            $builder->makeProtected();
        } else {
            $builder->makePublic();
        }
        if ($constant->isFinal()) {
            $builder->makeFinal();
        }
        return $builder->getNode();
    }

    private function buildProperty(ReflectionProperty $property): Stmt\Property
    {
        $builder = $this->builderFactory->property($property->getName());
        if ($doc = $property->getDocComment()) {
            $builder->setDocComment($doc);
        }
        assert(!$property->isPrivate());
        if ($property->isProtected()) {
            $builder->makeProtected();
        } else {
            $builder->makePublic();
        }
        if ($property->isStatic()) {
            $builder->makeStatic();
        }
        if ($property->isReadOnly()) {
            $builder->makeReadonly();
        }
        if ($property->hasType()) {
            $builder->setType($this->buildType($property->getType()));
        }
        if ($property->hasDefaultValue()) {
            if (self::canStubValue($value = $property->getDefaultValue())) {
                $builder->setDefault($value);
            } else {
                $this->logger?->warning("Cannot stub non-scalar default value of type `{type}` for property: `{name}`", [
                    'type' => get_debug_type($value),
                    'name' => sprintf('%s::%s', $property->getDeclaringClass()->getName(), $property->getName()),
                ]);
            }
        }
        return $builder->getNode();
    }

    private function buildMethod(ReflectionMethod $method): Stmt\ClassMethod
    {
        $builder = $this->builderFactory->method($method->getName());
        if ($doc = $method->getDocComment()) {
            $builder->setDocComment($doc);
        }
        if ($method->getDeclaringClass()->isInterface()) {
            $builder->makePublic();
        } else {
            if ($method->isFinal()) {
                $builder->makeFinal();
            } elseif ($method->isAbstract()) {
                $builder->makeAbstract();
            }
            assert(!$method->isPrivate());
            if ($method->isProtected()) {
                $builder->makeProtected();
            } else {
                $builder->makePublic();
            }
        }
        if ($method->isStatic()) {
            $builder->makeStatic();
        }
        if ($method->hasReturnType()) {
            $builder->setReturnType($this->buildType($method->getReturnType()));
        }
        foreach ($method->getParameters() as $parameter) {
            $builder->addParam($this->buildParameter($parameter));
        }
        return $builder->getNode();
    }

    private function buildParameter(ReflectionParameter $parameter): Node\Param
    {
        $builder = $this->builderFactory->param($parameter->getName());
        if ($parameter->hasType()) {
            $builder->setType($this->buildType($parameter->getType()));
        }
        if ($parameter->isVariadic()) {
            $builder = $builder->makeVariadic();
        }
        if ($parameter->isPassedByReference()) {
            $builder = $builder->makeByRef();
        }
        // TODO: check reflection.c to see how we can make them available
        if ($parameter->isOptional() && $parameter->isDefaultValueAvailable()) {
            if (self::canStubValue($value = $parameter->getDefaultValue())) {
                $builder->setDefault($value);
            } else {
                $this->logger?->warning("Cannot stub non-scalar default value of type `{type}` for parameter: `{name}` in `{function}`", [
                    'type' => get_debug_type($value),
                    'name' => $parameter->getName(),
                    'function' => $parameter->getDeclaringFunction()->getName(),
                ]);
            }
        }

        return $builder->getNode();
    }

    private function buildType(ReflectionType $type): string
    {
        // TODO: check behaviour w/ nullable types
        if ($type instanceof ReflectionNamedType) {
            $name = match ($type->isBuiltin()) {
                true => $type->getName(),
                false => match ($name = $type->getName()) {
                    'self', 'static', 'parent' => $name,
                    default => $this->resolveName($name),
                },
            };
            if ($type->allowsNull() && $name !== 'null') {
                return "{$name}|null";
            }
            return $name;
        }
        if ($type instanceof ReflectionIntersectionType) {
            return implode('&', array_map($this->buildType(...), $type->getTypes()));
        }
        assert($type instanceof ReflectionUnionType);
        $types = [];
        foreach ($type->getTypes() as $type) {
            $s = $this->buildType($type);
            if ($type instanceof ReflectionIntersectionType) {
                $types[] = "({$s})";
            } else {
                $types[] = $s;
            }

        }
        return implode('|', $types);
    }

    /**
     * @param string[] $names
     * @return string[]
     */
    private function resolveNames(array $names): array
    {
        return array_map($this->resolveName(...), $names);
    }

    private function resolveName(string $name): string
    {
        if (str_starts_with($name, '\\')) {
            // fully-qualified
            return $name;
        }
        if ($this->currentNamespace->containsName($name)) {
            [, $tail] = ReflectionUtils::splitName($name);
            return $tail;
        }
        return "\\{$name}";
    }

    private function maybeMarkAsAttribute(ReflectionClass $class, Builder\Class_ $classBuilder): void
    {
        if ($attr = $class->getAttributes(Attribute::class)[0] ?? null) {
            $builder = $this->builderFactory->attribute('\\Attribute', [
                'flags' => ReflectionUtils::getAttributeFlags($attr->newInstance(), $this->builderFactory),
            ]);
            $classBuilder->addAttribute($builder);
        }
    }

    private static function canStubValue(mixed $value): bool
    {
        return $value === null || \is_scalar($value) || \is_array($value);
    }
}
