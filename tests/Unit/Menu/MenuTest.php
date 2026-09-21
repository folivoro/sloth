<?php

declare(strict_types=1);

namespace Sloth\Tests\Unit\Menu;

use Sloth\Menu\Menu;

/**
 * Unit tests for the Menu service.
 */
describe('Menu service', function (): void {
    describe('locations()', function (): void {
        it('returns the registered location map', function (): void {
            app()->instance('sloth.menus', ['primary' => 'Primary Navigation']);
            $service = new Menu(app());

            expect($service->locations())->toBe(['primary' => 'Primary Navigation']);
        });

        it('returns an empty array when no menus are registered', function (): void {
            app()->instance('sloth.menus', []);
            $service = new Menu(app());

            expect($service->locations())->toBe([]);
        });
    });
});