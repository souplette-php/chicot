<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt;
use ReflectionClassConstant;

final readonly class ClassConstantBuilder
{
    public function __construct(
        private BuilderFactory $builderFactory = new BuilderFactory(),
        private TypeBuilder $typeBuilder = new TypeBuilder(),
    ) {
    }

    public function build(ReflectionClassConstant $constant): Stmt\ClassConst
    {
        $builder = $this->builderFactory->classConst($constant->getName(), $constant->getValue());
        if ($doc = $constant->getDocComment()) {
            $builder->setDocComment($doc);
        }
        if ($type = $constant->getType()) {
            $builder->setType($this->typeBuilder->build($type));
        }
        if ($constant->isPrivate()) {
            throw new \LogicException('Private constants should not be stubbed.');
        }
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
}
