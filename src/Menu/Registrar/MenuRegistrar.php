<?php

declare(strict_types=1);
namespace Sloth\Menu\Registrar;

use function register_nav_menu;
use Sloth\Menu\Manifest\MenuManifestBuilder;

/**
 * Registers WordPress navigation menus from manifest entries.
 *
 * Reads the pre-computed entry data from MenuManifestBuilder and calls
 * register_nav_menu() for each discovered menu class. No discovery
 * or argument-building overhead occurs at runtime — the manifest file
 * (cached by Opcache) provides all necessary data.
 *
 * ## Registration flow
 *
 * 1. MenuManifestBuilder discovers Menu subclasses on the `init` hook.
 * 2. Build-time: location and name are computed and cached.
 * 3. MenuRegistrar reads the cached entries and registers each menu
 *    with WordPress via register_nav_menu().
 *
 * ## Entry data structure
 *
 * Each entry contains:
 * - **location**: the WordPress nav menu location slug (e.g. 'primary')
 * - **name**: the display name (e.g. 'Primary Navigation')
 *
 * ## Design notes
 *
 * This class is intentionally thin — all the expensive computation happens
 * in MenuManifestBuilder at build time. The Registrar's job is purely
 * to call the WordPress API with pre-computed data.
 *
 * @since 1.0.0
 * @see MenuManifestBuilder For entry data computation
 * @see \Sloth\Menu\MenuServiceProvider            For hook registration
 */
class MenuRegistrar
{
    /**
     * Creates a new MenuRegistrar instance.
     *
     * @param MenuManifestBuilder $builder the manifest builder that provides
     *                                     the pre-computed entry data
     *
     * @since 1.0.0
     */
    public function __construct(
        private readonly MenuManifestBuilder $builder,
    ) {
    }

    /**
     * Register all discovered menus with WordPress.
     *
     * Iterates over the manifest entries and calls register_nav_menu()
     * for each menu class. This method is called on the WordPress `init`
     * hook via MenuServiceProvider::initMenus().
     *
     * @since 1.0.0
     */
    public function register(): void
    {
        foreach ($this->builder->getEntries() as $entry) {
            register_nav_menu(
                $entry['location'],
                $entry['name'],
            );
        }
    }
}
