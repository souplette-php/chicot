<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt;
use ReflectionClass;
use ReflectionClassConstant;
use Souplette\Chicot\Internal\NameResolver;
use Souplette\Chicot\Internal\ReflectionUtils;

final readonly class InterfaceBuilder
{
    public function __construct(
        private BuilderFactory $builderFactory,
        private NameResolver $nameResolver,
        private ClassConstantBuilder $classConstantBuilder,
        private FunctionLikeBuilder $functionBuilder,
    ) {
    }

    public function build(ReflectionClass $interface): Stmt\Interface_
    {
        $builder = $this->builderFactory->interface($interface->getShortName());
        if ($doc = $interface->getDocComment()) {
            $builder->setDocComment($doc);
        }
        $builder->extend(...$this->nameResolver->resolveMany(ReflectionUtils::getOwnInterfaceNames($interface)));
        foreach ($interface->getConstants() as $name => $value) {
            $constant = new ReflectionClassConstant($interface->getName(), $name);
            $builder->addStmt($this->classConstantBuilder->build($constant));
        }
        foreach (ReflectionUtils::getOwnMethods($interface) as $method) {
            $builder->addStmt($this->functionBuilder->buildMethod($method));
        }
        return $builder->getNode();
    }
}
