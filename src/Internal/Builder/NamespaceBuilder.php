<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use Psr\Log\LoggerInterface;
use ReflectionEnum;
use Souplette\Chicot\Internal\NameResolver;
use Souplette\Chicot\Internal\ReflectionNamespace;
use Souplette\Chicot\Internal\ReflectionUtils;

final readonly class NamespaceBuilder
{
    public function __construct(
        private BuilderFactory $builderFactory,
        private NameResolver $nameResolver,
        private FunctionLikeBuilder $functionBuilder,
        private InterfaceBuilder $interfaceBuilder,
        private EnumBuilder $enumBuilder,
        private ClassBuilder $classBuilder,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function build(ReflectionNamespace $namespace): Stmt\Namespace_
    {
        $this->nameResolver->setCurrentNamespace($namespace);
        $builder = $this->builderFactory->namespace(match ($name = $namespace->name) {
            '' => null,
            default => $name,
        });
        foreach ($namespace->getConstants() as $name => $value) {
            if (!ReflectionUtils::canStubValue($value)) {
                $this->logger?->warning("Cannot stub non-scalar value of type `{type}` for constant: `{name}`", [
                    'type' => get_debug_type($value),
                    'name' => $name,
                ]);
                continue;
            }
            $builder->addStmt($this->buildConstant($name, $value));
        }
        foreach ($namespace->getFunctions() as $function) {
            $builder->addStmt($this->functionBuilder->buildFunction($function));
        }
        foreach ($namespace->getClasses() as $class) {
            if ($class->isInterface()) {
                $builder->addStmt($this->interfaceBuilder->build($class));
            } else if ($class instanceof ReflectionEnum) {
                $builder->addStmt($this->enumBuilder->build($class));
            } else {
                $builder->addStmt($this->classBuilder->build($class));
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
}
