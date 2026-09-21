<?php

declare(strict_types=1);
namespace Sloth\Menu\Manifest;

use Override;
use Sloth\Model\Menu;
use Sloth\Support\Manifest\ClassMapFinder;
use Sloth\Support\Manifest\FinderInterface;
use Sloth\Support\Manifest\PathBasedManifestBuilder;

/**
 * Builds a manifest for WordPress navigation menu registration.
 *
 * Scans app/Menu/ and theme/Menu/ for Menu subclasses that define
 * static $location and $name properties. These are used to automatically
 * register WordPress navigation menu locations.
 *
 * ## Discovery
 *
 * Uses ClassMapFinder to locate all non-abstract classes extending
 * Sloth\Model\Menu. Each discovered class is inspected for its static
 * $location and $name properties.
 *
 * ## Build-time computation
 *
 * The expensive work — building registration args — happens once at build
 * time and is cached in the manifest file. At runtime, MenuRegistrar reads
 * this data and calls register_nav_menu() directly.
 *
 * ## Entry data structure
 *
 * ```php
 * [
 *     '\\App\\Model\\PrimaryMenu' => [
 *         'location' => 'primary',
 *         'name'     => 'Primary Navigation',
 *     ],
 * ]
 * ```
 *
 * Menu classes without $location or $name are excluded from the entries.
 *
 * @since 1.0.0
 * @see PathBasedManifestBuilder For the base class lifecycle
 * @see MenuRegistrar            For runtime registration
 */
class MenuManifestBuilder extends PathBasedManifestBuilder
{
    /**
     * Return the finder for Menu subclass discovery.
     *
     * Uses ClassMapFinder filtered to classes extending Sloth\Model\Menu.
     * Non-abstract subclasses are included; abstract base classes are excluded.
     *
     * @return FinderInterface the configured ClassMapFinder
     *
     * @since 1.0.0
     */
    #[Override]
    protected function finder(): FinderInterface
    {
        return new ClassMapFinder(Menu::class);
    }

    /**
     * Return the subdirectory name for Menu files.
     *
     * Scans `app/Menu/` and `theme/Menu/` — menu classes live in their own
     * dedicated directory, mirroring `Model` and `Taxonomy`.
     *
     * @return string always 'Menu'
     *
     * @since 1.0.0
     */
    #[Override]
    protected function directory(): string
    {
        return 'Menu';
    }

    /**
     * Compute registration entry data for all discovered menu classes.
     *
     * Iterates over each discovered Menu class and extracts the static
     * $location and $name properties. Classes without $location are skipped.
     *
     * @param  array<string, string>                                $map menu class name => absolute file path
     * @return array<string, array{location: string, name: string}> entry data keyed by class name
     *
     * @since 1.0.0
     */
    #[Override]
    protected function entries(array $map): array
    {
        $entries = [];

        /** @var class-string<Menu> $menuClass */
        foreach (array_keys($map) as $menuClass) {
            $location = $menuClass::$location ?? null;
            $name = $menuClass::$name ?? null;

            if ($location === null || $name === null) {
                continue;
            }

            $entries[$menuClass] = [
                'location' => $location,
                'name'     => $name,
            ];
        }

        return $entries;
    }
}
