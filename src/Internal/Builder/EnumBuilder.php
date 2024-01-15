<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt;
use ReflectionClassConstant;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use Souplette\Chicot\Internal\ReflectionUtils;

final readonly class EnumBuilder
{
    public function __construct(
        private BuilderFactory $builderFactory,
        private ClassConstantBuilder $classConstantBuilder,
        private FunctionLikeBuilder $functionBuilder,
    ) {
    }

    public function build(ReflectionEnum $enum): Stmt\Enum_
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
            $builder->addStmt($this->classConstantBuilder->build($constant));
        }
        foreach (ReflectionUtils::getOwnMethods($enum) as $method) {
            if (ReflectionUtils::isImplicitEnumMethod($method)) {
                continue;
            }
            $builder->addStmt($this->functionBuilder->buildMethod($method));
        }
        return $builder->getNode();
    }
}
