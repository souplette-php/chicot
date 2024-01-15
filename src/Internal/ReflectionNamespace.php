<?php declare(strict_types=1);

namespace Souplette\Chicot\Internal;

use ReflectionClass;
use ReflectionFunction;
use Souplette\Chicot\Internal\Contracts\ContainsName;

final class ReflectionNamespace implements ContainsName
{
    /** @var array<string, mixed> */
    private array $constants = [];

    /** @var ReflectionFunction[] */
    private array $functions = [];

    /** @var ReflectionClass[] */
    private array $classes = [];

    public function __construct(
        public readonly string $name,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function containsName(string $name): bool
    {
        return (
            isset($this->constants[$name])
            || isset($this->functions[$name])
            || isset($this->classes[$name])
        );
    }

    public function addConstant(string $name, mixed $value): void
    {
        $this->constants[$name] = $value;
    }

    public function addFunction(ReflectionFunction $function): void
    {
        $this->functions[$function->getName()] = $function;
    }

    public function addClass(ReflectionClass $class): void
    {
        $this->classes[$class->getName()] = $class;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    /**
     * @return array<string, ReflectionFunction>
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @return array<string, ReflectionClass>
     */
    public function getClasses(): array
    {
        return $this->classes;
    }
}
