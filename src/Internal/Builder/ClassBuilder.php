<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal\Builder;

use Attribute;
use PhpParser\Builder;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Souplette\Chicot\Internal\NameResolver;
use Souplette\Chicot\Internal\ReflectionUtils;

final readonly class ClassBuilder
{
    public function __construct(
        private BuilderFactory $builderFactory,
        private NameResolver $nameResolver,
        private ClassConstantBuilder $classConstantBuilder,
        private PropertyBuilder $propertyBuilder,
        private FunctionLikeBuilder $functionBuilder,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function build(ReflectionClass $class): Stmt\Class_
    {
        $builder = $this->builderFactory->class($class->getShortName());
        if ($doc = $class->getDocComment()) {
            $builder->setDocComment($doc);
        }
        if ($class->isFinal()) {
            $builder->makeFinal();
        } else if ($class->isAbstract()) {
            $builder->makeAbstract();
        }
        if ($class->isReadOnly()) {
            $builder->makeReadonly();
        }
        $this->maybeMarkAsAttribute($class, $builder);
        if ($parent = $class->getParentClass()) {
            $builder->extend($this->nameResolver->resolve($parent->getName()));
        }
        $builder->implement(...$this->nameResolver->resolveMany(ReflectionUtils::getOwnInterfaceNames($class)));
        foreach (ReflectionUtils::getOwnConstants($class) as $constant) {
            if ($constant->isPrivate()) {
                continue;
            }
            if (!ReflectionUtils::canStubValue($value = $constant->getValue())) {
                $this->logger?->warning("Cannot stub non-scalar value of type `{type}` for class constant: `{name}`", [
                    'type' => get_debug_type($value),
                    'name' => sprintf('%s::%s', $class->getName(), $constant->getName()),
                ]);
                continue;
            }
            $builder->addStmt($this->classConstantBuilder->build($constant));
        }
        foreach (ReflectionUtils::getOwnProperties($class) as $property) {
            if ($property->isPrivate()) {
                continue;
            }
            $builder->addStmt($this->propertyBuilder->build($property));
        }
        foreach (ReflectionUtils::getOwnMethods($class) as $method) {
            if ($method->isPrivate()) {
                continue;
            }
            $builder->addStmt($this->functionBuilder->buildMethod($method));
        }
        return $builder->getNode();
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
}
