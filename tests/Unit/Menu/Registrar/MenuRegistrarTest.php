<?php

declare(strict_types=1);

namespace Sloth\Tests\Unit\Menu\Registrar;

use Sloth\Menu\Manifest\MenuManifestBuilder;
use Sloth\Menu\Registrar\MenuRegistrar;

/**
 * Unit tests for the MenuRegistrar class.
 */
describe('MenuRegistrar', function (): void {
    afterEach(function (): void {
        $GLOBALS['wp_nav_menu_calls'] = [];
    });

    describe('Construction', function (): void {
        it('can be instantiated with a manifest builder', function (): void {
            $builder = $this->createMock(MenuManifestBuilder::class);
            $registrar = new MenuRegistrar($builder);
            expect($registrar)->toBeInstanceOf(MenuRegistrar::class);
        });
    });

    describe('register()', function (): void {
        it('registers each manifest entry as a nav menu', function (): void {
            $builder = $this->createMock(MenuManifestBuilder::class);
            $builder->method('getEntries')->willReturn([
                '\\App\\Menu\\PrimaryMenu' => [
                    'location' => 'primary',
                    'name'     => 'Primary Navigation',
                ],
                '\\App\\Menu\\FooterMenu'  => [
                    'location' => 'footer',
                    'name'     => 'Footer Navigation',
                ],
            ]);

            $registrar = new MenuRegistrar($builder);
            $registrar->register();

            expect($GLOBALS['wp_nav_menu_calls'])->toBe([
                ['location' => 'primary', 'name' => 'Primary Navigation'],
                ['location' => 'footer', 'name' => 'Footer Navigation'],
            ]);
        });

        it('registers nothing when no entries exist', function (): void {
            $GLOBALS['wp_nav_menu_calls'] = [];

            $builder = $this->createMock(MenuManifestBuilder::class);
            $builder->method('getEntries')->willReturn([]);

            $registrar = new MenuRegistrar($builder);
            $registrar->register();

            expect($GLOBALS['wp_nav_menu_calls'])->toBe([]);
        });
    });
});