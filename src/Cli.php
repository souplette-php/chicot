<?php declare(strict_types=1);

namespace Souplette\Chicot;

use ReflectionExtension;

final class Cli
{
    public function run(array $argv): int
    {
        $name = $argv[1] ?? throw new \ArgumentCountError("Missing required argument <extension-name>");
        $outfile = $argv[2] ?? null;
        $ext = self::loadExtension($name);
        $code = StubsGenerator::generate($ext);
        if (!$outfile) {
            echo $code, "\n";
        } else {
            $file = new \SplFileObject($outfile, 'w');
            $file->fwrite($code);
        }
        return 0;
    }

    private static function loadExtension(string $name): ReflectionExtension
    {
        return new ReflectionExtension($name);
    }
}
