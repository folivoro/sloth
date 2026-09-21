<?php

declare(strict_types=1);
namespace Sloth\Tests\Unit\Model;

use ReflectionClass;
use Sloth\Model\MenuItem;

/*
 * Unit tests for the MenuItem serialization whitelist.
 *
 * These tests verify that toArray() output is kept lean for menu items
 * and that the resolved type/target/type_id accessors are exposed.
 */
describe('MenuItem', function (): void {
    describe('$visible', function (): void {
        it('keeps only menu-relevant fields in array output', function (): void {
            $reflection = new ReflectionClass(MenuItem::class);
            $visible = $reflection->getDefaultProperties()['visible'];

            expect($visible)->toBe([
                'ID',
                'title',
                'url',
                'target',
                'menu_order',
                'type',
                'type_id',
                'classes',
                'current',
                'current_item_parent',
                'current_item_ancestor',
                'in_current_path',
                'children',
            ]);
        });

        it('does not expose the raw meta relationship', function (): void {
            $reflection = new ReflectionClass(MenuItem::class);
            $visible = $reflection->getDefaultProperties()['visible'];

            expect($visible)->not->toContain('meta');
            expect($visible)->not->toContain('pivot');
            expect($visible)->not->toContain('acf');
            expect($visible)->not->toContain('post_content');
        });
    });

    describe('type accessors', function (): void {
        /**
         * Build a MetaCollection of meta models for the given key/value pairs.
         *
         * @param  array<string, string>                   $pairs
         */
        function metaCollection(array $pairs): \Corcel\Model\Collection\MetaCollection
        {
            $items = array_map(
                fn ($value, $key): \Sloth\Model\Meta\PostMeta => new \Sloth\Model\Meta\PostMeta(['meta_key' => $key, 'meta_value' => $value]),
                $pairs,
                array_keys($pairs),
            );

            return new \Corcel\Model\Collection\MetaCollection($items);
        }

        it('resolves type from the _menu_item_type meta field', function (): void {
            $item = new MenuItem();
            $item->setRelation('meta', metaCollection(['_menu_item_type' => 'post_type']));

            expect($item->type)->toBe('post_type');
        });

        it('resolves type_id from the _menu_item_object_id meta field', function (): void {
            $item = new MenuItem();
            $item->setRelation('meta', metaCollection(['_menu_item_object_id' => '186']));

            expect($item->type_id)->toBe(186);
        });

        it('resolves target from the _menu_item_target meta field', function (): void {
            $item = new MenuItem();
            $item->setRelation('meta', metaCollection(['_menu_item_target' => '_blank']));

            expect($item->target)->toBe('_blank');
        });
    });
});
