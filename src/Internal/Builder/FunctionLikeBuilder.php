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
use Souplette\Chicot\Internal\ReflectionUtils;

final readonly class FunctionLikeBuilder
{
    public function __construct(
        private BuilderFactory $builderFactory,
        private TypeBuilder $typeBuilder,
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
        if ($type = $rf->getReturnType()) {
            $builder->setReturnType($this->typeBuilder->build($type));
        } else if ($type = $rf->getTentativeReturnType()) {
            $this->logger->warning('Tentative: {type} ({fn})', [
                'type' => $type,
                'fn' => $rf->getName(),
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
            $builder = $builder->makeVariadic();
        }
        if ($parameter->isPassedByReference()) {
            $builder = $builder->makeByRef();
        }
        // TODO: check reflection.c to see how we can make them available
        if ($parameter->isOptional() && $parameter->isDefaultValueAvailable()) {
            if (ReflectionUtils::canStubValue($value = $parameter->getDefaultValue())) {
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
}
