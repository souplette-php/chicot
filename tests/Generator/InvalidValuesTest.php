<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests\Generator;

use ReflectionClass;
use Souplette\Chicot\Mocks\InvalidValues;
use Souplette\Chicot\Tests\GeneratorTestCase;

final class InvalidValuesTest extends GeneratorTestCase
{
    public function testInvalidValuesAreSkipped(): void
    {
        $code = self::generateStubs(
            'acme',
            constants: [
                'VALID' => true,
                'INVALID' => \STDERR,
            ],
            classes: [new ReflectionClass(InvalidValues::class)],
        );
        $expected = <<<'PHP'
        namespace {
            const VALID = true;
        }

        namespace Souplette\Chicot\Mocks {
            final class InvalidValues
            {
                public static $invalid;
                public $invalidProp;
                public function invalid($invalid) : void {}
            }
        }
        PHP;
        self::assertCodeEq($expected, $code);
    }
}
