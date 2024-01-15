<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt;
use Psr\Log\LoggerInterface;
use ReflectionExtension;
use Souplette\Chicot\Internal\Builder\ClassBuilder;
use Souplette\Chicot\Internal\Builder\ClassConstantBuilder;
use Souplette\Chicot\Internal\Builder\EnumBuilder;
use Souplette\Chicot\Internal\Builder\FunctionLikeBuilder;
use Souplette\Chicot\Internal\Builder\InterfaceBuilder;
use Souplette\Chicot\Internal\Builder\NamespaceBuilder;
use Souplette\Chicot\Internal\Builder\PropertyBuilder;
use Souplette\Chicot\Internal\Builder\TypeBuilder;

final class ExtensionBuilder
{
    private NamespaceBuilder $namespaceBuilder;

    public function __construct(
        private readonly ?LoggerInterface $logger = null,
    ) {
        $factory = new BuilderFactory();
        $resolver = new NameResolver();
        $typeBuilder = new TypeBuilder($resolver);
        $functionBuilder = new FunctionLikeBuilder($factory, $typeBuilder, $this->logger);
        $propertyBuilder = new PropertyBuilder($factory, $typeBuilder, $this->logger);
        $classConstantBuilder = new ClassConstantBuilder($factory, $typeBuilder);
        $this->namespaceBuilder = new NamespaceBuilder(
            $factory,
            $resolver,
            $functionBuilder,
            new InterfaceBuilder($factory, $resolver, $classConstantBuilder, $functionBuilder),
            new EnumBuilder($factory, $classConstantBuilder, $functionBuilder),
            new ClassBuilder($factory, $resolver, $classConstantBuilder, $propertyBuilder, $functionBuilder, $this->logger),
            $this->logger,
        );
    }

    /**
     * @return Stmt\Namespace_[]
     */
    public function build(ReflectionExtension $ext): array
    {
        $namespaces = NamespaceCollector::collect($ext);
        $nodes = [];
        foreach ($namespaces as $namespace) {
            $nodes[] = $this->namespaceBuilder->build($namespace);
        }
        return $nodes;
    }
}
