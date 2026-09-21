<?php

declare(strict_types=1);
namespace Sloth\Model;

use function array_search;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Base Menu class for WordPress navigation menus.
 *
 * Extends Sloth\Model\Taxonomy directly to provide WordPress navigation
 * menu support without Corcel. WordPress navigation menus are technically
 * terms of the 'nav_menu' taxonomy stored on the term_taxonomy table.
 *
 * ## Menu class registration
 *
 * Theme developers create Menu subclasses that declare static $location
 * and $name properties. These are picked up by MenuManifestBuilder and
 * registered as WordPress navigation menu locations:
 *
 * ```php
 * class PrimaryMenu extends Menu
 * {
 *     public static $location = 'primary';
 *     public static $name     = 'Primary Navigation';
 * }
 * ```
 *
 * ## Example
 *
 * ```php
 * // Get menu items by location (static convenience)
 * foreach (MainMenu::items() as $item) {
 *     echo $item->title;
 * }
 *
 * // Get a single menu instance
 * $menu = Menu::location('primary')->first();
 *
 * // Get menu items from an instance
 * foreach ($menu->menuItems as $item) {
 *     echo $item->title;
 * }
 * ```
 *
 * @since 1.0.0
 * @see \Sloth\Menu\Manifest\MenuManifestBuilder For menu class discovery
 * @see \Sloth\Menu\Registrar\MenuRegistrar       For menu registration
 */
class Menu extends Taxonomy
{
    // -------------------------------------------------------------------------
    // Registration properties
    //
    // Intentionally untyped static properties. Theme developers override these
    // in child classes without type declarations to avoid PHP 8.4 typed
    // property inheritance errors. PHPStan reads the @var DocBlocks below.
    //
    // The MenuManifestBuilder reads these via static access: PrimaryMenu::$location
    // -------------------------------------------------------------------------

    /**
     * The WordPress nav menu location slug.
     *
     * e.g. 'primary', 'footer', 'mobile'. When set (together with $name)
     * on a Menu subclass, the menu is automatically registered as a
     * WordPress navigation menu location.
     *
     * @since 1.0.0
     *
     * @var string|null
     */
    public static $location = null;

    /**
     * The human-readable display name for the menu location.
     *
     * e.g. 'Primary Navigation'. Shown in the WordPress admin under
     * Appearance → Menus when the location is registered.
     *
     * @since 1.0.0
     *
     * @var string|null
     */
    public static $name = null;

    // -------------------------------------------------------------------------
    // Corcel-inherited properties
    // -------------------------------------------------------------------------

    /**
     * The WordPress taxonomy identifier for navigation menus.
     *
     * @since 1.0.0
     */
    protected ?string $taxonomy = 'nav_menu';

    /**
     * Relationships to eager-load on every query.
     *
     * @var array<string>
     */
    protected $with = ['term', 'menuItems'];

    // -------------------------------------------------------------------------
    // Query helpers
    // -------------------------------------------------------------------------

    /**
     * Gets a menu by its WordPress location.
     *
     * @since 1.0.0
     *
     * @param  string                                     $location_name The menu location identifier
     * @return \Illuminate\Database\Eloquent\Builder|self The menu query builder
     *
     * @uses get_nav_menu_locations() To find the menu ID for the location
     */
    public static function location(string $location_name): self|\Illuminate\Database\Eloquent\Builder
    {
        $id = get_nav_menu_locations()[$location_name] ?? null;

        if ($id === null) {
            return static::query()->whereNull('term_taxonomy_id');
        }

        return static::query()->where('term_taxonomy_id', $id);
    }

    /**
     * Gets the menu items of the menu registered for this class's location.
     *
     * Convenience for the most common use case in theme templates and API
     * controllers. Reads the location declared on the menu class (`$location`)
     * and returns the menu items — or an empty collection when no menu is
     * assigned to that location yet.
     *
     * ```php
     * foreach (MainMenu::items() as $item) {
     *     echo $item->title;
     * }
     * ```
     *
     * @since 1.0.0
     *
     * @return EloquentCollection<int, MenuItem> the menu items
     */
    public static function items(): EloquentCollection
    {
        if (static::$location === null) {
            return new EloquentCollection();
        }

        $menu = static::location(static::$location)->first();

        return $menu?->menuItems ?? new EloquentCollection();
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Gets the menu items for this menu.
     *
     * @since 1.0.0
     *
     * @return BelongsToMany The items relationship
     */
    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(
            MenuItem::class,
            'term_relationships',
            'term_taxonomy_id',
            'object_id',
        )->orderBy('menu_order');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Gets the location name for this menu.
     *
     * @since 1.0.0
     *
     * @return false|string The location name or false if not found
     */
    public function getLocationAttribute(): false|string
    {
        $location = array_search($this->term_taxonomy_id, get_nav_menu_locations(), true);

        return $location === false ? false : $location;
    }
}
