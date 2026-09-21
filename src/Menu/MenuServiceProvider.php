<?php

declare(strict_types=1);
namespace Sloth\Menu;

use Override;
use Sloth\Core\ServiceProvider;
use Sloth\Menu\Manifest\MenuManifestBuilder;
use Sloth\Menu\Registrar\MenuRegistrar;

/**
 * Service provider for navigation menu registration.
 *
 * Discovers menu classes (subclasses of \Sloth\Model\Menu that define
 * static $location and $name properties) and registers them as WordPress
 * navigation menu locations.
 *
 * ## Responsibilities
 *
 * - **Discovery**: Delegates to MenuManifestBuilder for scanning app/Menu/
 *   and theme/Menu/ for Menu subclasses.
 * - **Registration**: Delegates to MenuRegistrar for calling
 *   register_nav_menu() with pre-computed location/name pairs.
 * - **Container bindings**: Populates `sloth.menus` with the registered
 *   menu locations.
 *
 * ## Hook execution order
 *
 * 1. `init` → MenuManifestBuilder::init() + MenuRegistrar::register()
 *
 * ## Container bindings
 *
 * - **sloth.menus**: Maps location slugs to their display names.
 *
 * @since 1.0.0
 * @see MenuManifestBuilder For menu class discovery
 * @see MenuRegistrar       For WordPress menu registration
 */
class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register the Menu service provider.
     *
     * Binds the manifest builder and registrar as singletons.
     *
     * @since 1.0.0
     */
    #[Override]
    public function register(): void
    {
        $this->app->singleton(MenuManifestBuilder::class, fn ($app): MenuManifestBuilder => new MenuManifestBuilder($app));
        $this->app->singleton(MenuRegistrar::class, fn ($app): MenuRegistrar => new MenuRegistrar(app(MenuManifestBuilder::class)));
        $this->app->singleton('menu', fn ($app): Menu => new Menu($app));
    }

    /**
     * Register WordPress action hooks for menu registration.
     *
     * @return array<string, array<callable>|callable> hook mappings
     *
     * @since 1.0.0
     */
    #[Override]
    public function getHooks(): array
    {
        return [
            'init' => $this->initMenus(...),
        ];
    }

    /**
     * Initialize menus: discover, register, and bind to container.
     *
     * Orchestrates the menu lifecycle:
     * 1. Runs MenuManifestBuilder::init() (discovery + manifest loading).
     * 2. Merges legacy config-based menus (config('theme.menus')) with the
     *    discovered model-based menus. Discovered entries take precedence.
     * 3. Binds sloth.menus to the container (location => name map).
     * 4. Calls MenuRegistrar::register() to register with WordPress.
     *
     * @since 1.0.0
     */
    protected function initMenus(): void
    {
        $builder = app(MenuManifestBuilder::class);
        $builder->init();

        $entries = collect($builder->getEntries());

        foreach (config('theme.menus', []) as $location => $name) {
            $entries->put("config:{$location}", [
                'location' => $location,
                'name'     => $name,
            ]);
        }

        $this->app->instance('sloth.menus', $entries
            ->mapWithKeys(fn ($entry): array => [$entry['location'] => $entry['name']])
            ->all());

        app(MenuRegistrar::class)->register();
    }
}
