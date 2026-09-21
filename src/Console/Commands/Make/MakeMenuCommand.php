<?php

declare(strict_types=1);
namespace Sloth\Console\Commands\Make;

use Illuminate\Support\Str;
use Override;

/**
 * Generate a new Menu.
 *
 * Creates a Menu model subclass in the app/ or theme/ directory that the
 * MenuManifestBuilder picks up to register a WordPress nav menu location.
 *
 * @since 1.0.0
 */
class MakeMenuCommand extends MakeCommand
{
    protected $signature = 'make:menu {name : The menu name}';

    protected $description = 'Create a new Menu';

    #[Override]
    protected function stub(): string
    {
        return 'Menu.php.stub';
    }

    #[Override]
    protected function destination(): string
    {
        return app()->basePath();
    }

    #[Override]
    protected function classSuffix(): string
    {
        return 'Menu';
    }

    #[Override]
    protected function outputPath(string $name): string
    {
        $class = $this->resolveClass($name);

        return "Menu/{$class}.php";
    }

    #[Override]
    protected function replacements(string $name): array
    {
        $class = $this->resolveClass($name);
        $base = Str::replaceLast('Menu', '', $class);

        return [
            '{{ location }}' => Str::kebab($base),
            '{{ name }}'     => Str::headline($base),
        ];
    }
}
