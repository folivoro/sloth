<?php

declare(strict_types=1);

namespace Sloth\Tests\Unit\Menu\Manifest;

use Sloth\Menu\Manifest\MenuManifestBuilder;

/**
 * Unit tests for the MenuManifestBuilder.
 */
describe('MenuManifestBuilder', function (): void {
    it('is instantiable with the application', function (): void {
        $builder = new MenuManifestBuilder(app());
        expect($builder)->toBeInstanceOf(MenuManifestBuilder::class);
    });

    it('scans the dedicated Menu directory', function (): void {
        $builder = new MenuManifestBuilder(app());
        $method = new \ReflectionMethod($builder, 'directory');

        expect($method->invoke($builder))->toBe('Menu');
    });
});