<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal\Builder;

use PhpParser\Builder;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use Psr\Log\LoggerInterface;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use Souplette\Chicot\Internal\NameResolver;
use Souplette\Chicot\Internal\ReflectionUtils;

final readonly class FunctionLikeBuilder
{
    public function __construct(
        private BuilderFactory $builderFactory = new BuilderFactory(),
        private NameResolver $nameResolver = new NameResolver(),
        private TypeBuilder $typeBuilder = new TypeBuilder(),
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function buildFunction(ReflectionFunction $function): Stmt\Function_
    {
        $builder = $this->builderFactory->function($function->getShortName());
        if ($doc = $function->getDocComment()) {
            $builder->setDocComment($doc);
        }
        $this->buildReturnType($builder, $function);
        foreach ($function->getParameters() as $parameter) {
            $builder->addParam($this->buildParameter($parameter));
        }
        return $builder->getNode();
    }

    public function buildMethod(ReflectionMethod $method): Stmt\ClassMethod
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
            if ($method->isPrivate()) {
                throw new \LogicException('Private methods should not be stubbed.');
            }
            if ($method->isProtected()) {
                $builder->makeProtected();
            } else {
                $builder->makePublic();
            }
        }
        if ($method->isStatic()) {
            $builder->makeStatic();
        }
        $this->buildReturnType($builder, $method);
        foreach ($method->getParameters() as $parameter) {
            $builder->addParam($this->buildParameter($parameter));
        }
        return $builder->getNode();
    }

    private function buildReturnType(Builder\FunctionLike $builder, ReflectionFunctionAbstract $rf): void
    {
        if ($rf->returnsReference()) {
            $builder->makeReturnByRef();
        }
        if ($type = $rf->getReturnType()) {
            $builder->setReturnType($this->typeBuilder->build($type));
        } else if ($type = $rf->getTentativeReturnType()) {
            $this->logger?->info('Tentative return type: {fn}: {type}', [
                'type' => $type,
                'fn' => self::getFullName($rf),
            ]);
            $builder->setReturnType($this->typeBuilder->build($type));
        }
    }

    private function buildParameter(ReflectionParameter $parameter): Node\Param
    {
        $builder = $this->builderFactory->param($parameter->getName());
        if ($type = $parameter->getType()) {
            $builder->setType($this->typeBuilder->build($type));
        }
        if ($parameter->isVariadic()) {
            $builder->makeVariadic();
        }
        if ($parameter->isPassedByReference()) {
            $builder->makeByRef();
        }
        if ($parameter->isOptional()) {
            if ($parameter->isDefaultValueAvailable()) {
                if ($const = $parameter->getDefaultValueConstantName()) {
                    $builder->setDefault($this->buildConstFetch($const));
                } else if (ReflectionUtils::canStubValue($value = $parameter->getDefaultValue())) {
                    $builder->setDefault($value);
                } else {
                    $this->logger?->warning("Cannot stub non-scalar default value of type `{type}` for parameter `{name}` of `{function}`", [
                        'type' => get_debug_type($value),
                        'name' => $parameter->getName(),
                        'function' => self::getFullName($parameter->getDeclaringFunction()),
                    ]);
                }
            } else {
                // TODO: check reflection.c to see how we can make them available
                $this->logger?->warning("Default value not available for parameter `{name}` of `{function}`", [
                    'name' => $parameter->getName(),
                    'function' => self::getFullName($parameter->getDeclaringFunction()),
                ]);
            }
        }

        return $builder->getNode();
    }

    private function buildConstFetch(string $name): Node
    {
        $parts = explode('::', $name, 2);
        if (\count($parts) == 2) {
            [$class, $const] = $parts;
            return $this->builderFactory->classConstFetch($this->nameResolver->resolve($class), $const);
        }
        return $this->builderFactory->constFetch($this->nameResolver->resolve($name));
    }

    private static function getFullName(ReflectionFunctionAbstract $fn): string
    {
        if ($fn instanceof ReflectionMethod) {
            return sprintf('%s::%s()', $fn->getDeclaringClass()->getName(), $fn->getName());
        }
        return sprintf('%s()', $fn->getName());
    }
}
