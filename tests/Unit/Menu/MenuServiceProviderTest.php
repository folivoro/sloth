<?php

declare(strict_types=1);

namespace Sloth\Tests\Unit\Menu;

use Sloth\Core\ServiceProvider;
use Sloth\Menu\Manifest\MenuManifestBuilder;
use Sloth\Menu\MenuServiceProvider;
use Sloth\Menu\Registrar\MenuRegistrar;

/**
 * Unit tests for the MenuServiceProvider.
 */
describe('MenuServiceProvider', function (): void {
    describe('Registration', function (): void {
        it('is a service provider', function (): void {
            $app = app();
            $provider = new MenuServiceProvider($app);
            expect($provider)->toBeInstanceOf(ServiceProvider::class);
        });

        it('registers the manifest builder', function (): void {
            $app = app();
            $provider = new MenuServiceProvider($app);
            $provider->register();

            expect($app->bound(MenuManifestBuilder::class))->toBeTrue();
        });

        it('registers the registrar with the manifest builder', function (): void {
            $app = app();
            $provider = new MenuServiceProvider($app);
            $provider->register();

            $registrar = $app->make(MenuRegistrar::class);
            expect($registrar)->toBeInstanceOf(MenuRegistrar::class);
        });

        it('registers the menu container binding', function (): void {
            $app = app();
            $provider = new MenuServiceProvider($app);
            $provider->register();

            expect($app->bound('menu'))->toBeTrue();
        });
    });

    describe('Hooks', function (): void {
        it('registers a hook on the init action', function (): void {
            $app = app();
            $provider = new MenuServiceProvider($app);
            $provider->register();

            $hooks = $provider->getHooks();
            expect($hooks)->toHaveKey('init');
        });
    });
});