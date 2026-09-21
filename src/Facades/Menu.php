<?php

declare(strict_types=1);
namespace Sloth\Facades;

use Override;

/**
 * Menu Facade for accessing the menu service.
 *
 * Provides static access to registered navigation menu locations
 * and menu model queries. Delegates to the `menu` container binding,
 * which is an instance of \Sloth\Menu\Menu.
 *
 * ## Usage
 *
 * ```php
 * // List all registered locations (slug => name)
 * Menu::locations();
 *
 * // Query a menu by location
 * $menu = Menu::location('primary')->first();
 *
 * // Get all menus for registered locations
 * Menu::all();
 * ```
 *
 * @since 1.0.0
 * @see \Sloth\Menu\Menu For the underlying service
 * @see \Sloth\Menu\MenuServiceProvider For registration
 */
class Menu extends Facade
{
    /**
     * Return the service provider key responsible for the menu class.
     *
     * @since 1.0.0
     *
     * @return string The service identifier for the menu facade
     */
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return 'menu';
    }
}
