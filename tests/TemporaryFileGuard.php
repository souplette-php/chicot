<?php declare(strict_types=1);

namespace Souplette\Chicot\Tests;

final readonly class TemporaryFileGuard
{
    public string $path;

    public function __construct(string $prefix = 'chicot')
    {
        $this->path = tempnam(sys_get_temp_dir(), $prefix);
    }

    public function getContents(): string
    {
        return file_get_contents($this->path);
    }

    public function __destruct()
    {
        @unlink($this->path);
    }
}
