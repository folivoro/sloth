<?php

declare(strict_types=1);
namespace Sloth\Tests\Unit\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mockery;
use Sloth\Model\Menu;
use Sloth\Model\MenuItem;

/**
 * A menu subclass with a fixed location for testing.
 *
 * Overrides the static location() query so no database connection is
 * required — tests inject a mocked query builder via $mockQuery.
 */
class TestLocationMenu extends Menu
{
    public static $location = 'main';

    /**
     * @var Builder|null Injected query builder returned by location()
     */
    public static $mockQuery = null;

    public static function location(string $location_name): self|Builder
    {
        return static::$mockQuery ?? parent::location($location_name);
    }
}

/**
 * Unit tests for the Menu model.
 */
describe('Menu', function (): void {
    afterEach(function (): void {
        Mockery::close();
        TestLocationMenu::$mockQuery = null;
        $GLOBALS['wp_nav_menu_locations'] = [];
    });

    describe('items()', function (): void {
        it('returns an empty collection when no location is set', function (): void {
            $result = Menu::items();

            expect($result)->toBeInstanceOf(EloquentCollection::class);
            expect($result)->toBeEmpty();
        });

        it('returns an empty collection when no menu resolves for the location', function (): void {
            $builder = Mockery::mock(Builder::class);
            $builder->shouldReceive('first')->once()->andReturnNull();
            TestLocationMenu::$mockQuery = $builder;

            $result = TestLocationMenu::items();

            expect($result)->toBeInstanceOf(EloquentCollection::class);
            expect($result)->toBeEmpty();
        });

        it('returns the menu items of the resolved menu', function (): void {
            $items = new EloquentCollection([new MenuItem(), new MenuItem()]);

            $menu = new TestLocationMenu();
            $menu->setRelation('menuItems', $items);

            $builder = Mockery::mock(Builder::class);
            $builder->shouldReceive('first')->once()->andReturn($menu);
            TestLocationMenu::$mockQuery = $builder;

            $result = TestLocationMenu::items();

            expect($result)->toBe($items);
        });
    });

    describe('menuItems()', function (): void {
        it('defines a belongsToMany relationship through term_relationships', function (): void {
            $relation = Mockery::mock(BelongsToMany::class);
            $relation->shouldReceive('orderBy')->once()->with('menu_order')->andReturnSelf();

            $menu = Mockery::mock(TestLocationMenu::class)->makePartial();
            $menu->shouldReceive('belongsToMany')
                ->once()
                ->with(
                    MenuItem::class,
                    'term_relationships',
                    'term_taxonomy_id',
                    'object_id',
                )
                ->andReturn($relation);

            $menu->menuItems();
        });
    });
});