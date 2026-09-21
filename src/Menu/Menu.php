<?php

declare(strict_types=1);
namespace Sloth\Menu;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Sloth\Model\Menu as MenuModel;

/**
 * Menu service for navigation menu access.
 *
 * Provides a container-bound entry point for reading registered navigation
 * menu locations and querying menu models. Registered under the `menu`
 * key in the application container and exposed via the Menu facade.
 *
 * ## Registry access
 *
 * The registered menu locations (location => name) are stored in the
 * container as `sloth.menus` by MenuServiceProvider during the `init` hook.
 *
 * ## Usage
 *
 * ```php
 * // Via facade — list all registered locations
 * Menu::locations();
 *
 * // Query a specific menu by location
 * $menu = Menu::location('primary')->first();
 *
 * // Via container
 * app('menu')->locations();
 * ```
 *
 * @since 1.0.0
 * @see MenuServiceProvider For registration
 * @see Manifest\MenuManifestBuilder For discovery
 */
class Menu
{
    /**
     * Creates a new Menu service instance.
     *
     * @param mixed $app the application container
     *
     * @since 1.0.0
     */
    public function __construct(protected mixed $app)
    {
    }

    /**
     * Get all registered menu locations.
     *
     * @return array<string, string> map of location slug => display name
     *
     * @since 1.0.0
     */
    public function locations(): array
    {
        return $this->app['sloth.menus'] ?? [];
    }

    /**
     * Get a menu query builder for a registered location.
     *
     * @param  string  $location the menu location slug
     * @return Builder the query builder filtered to the given location
     *
     * @since 1.0.0
     */
    public function location(string $location): Builder
    {
        return MenuModel::location($location);
    }

    /**
     * Get all menus assigned to registered locations.
     *
     * @return Collection menu models for each registered location
     *
     * @since 1.0.0
     */
    public function all(): Collection
    {
        return collect($this->locations())
            ->map(fn ($name, string $location) => $this->location($location)->first())
            ->filter()
        ;
    }
}
