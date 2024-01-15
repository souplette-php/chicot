<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt;
use Psr\Log\LoggerInterface;
use ReflectionProperty;
use Souplette\Chicot\Internal\ReflectionUtils;

final readonly class PropertyBuilder
{
    public function __construct(
        private BuilderFactory $builderFactory,
        private TypeBuilder $typeBuilder,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function build(ReflectionProperty $property): Stmt\Property
    {
        $builder = $this->builderFactory->property($property->getName());
        if ($doc = $property->getDocComment()) {
            $builder->setDocComment($doc);
        }
        if ($property->isPrivate()) {
            throw new \LogicException('Private properties should not be stubbed.');
        }
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
        if ($type = $property->getType()) {
            $builder->setType($this->typeBuilder->build($type));
        }
        if ($property->hasDefaultValue()) {
            if (ReflectionUtils::canStubValue($value = $property->getDefaultValue())) {
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
}
