<?php

declare(strict_types=1);
namespace Sloth\Tests\Unit\Model\Meta;

use Sloth\Model\Meta\PostMeta;

/*
 * Unit tests for the HasSafeMetaValue trait.
 *
 * These tests verify that the meta `value` accessor returns meta values
 * without triggering PHP 8 `unserialize()` warnings for plain-text values.
 */
describe('HasSafeMetaValue', function (): void {
    it('returns plain text meta values untouched', function (): void {
        $meta = new PostMeta();
        $meta->setRawAttributes(['meta_value' => '123456789']);

        expect($meta->value)->toBe('123456789');
    });

    it('returns plain text menu item values untouched', function (): void {
        $meta = new PostMeta();
        $meta->setRawAttributes(['meta_value' => 'nav_menu']);

        expect($meta->value)->toBe('nav_menu');
    });

    it('unserializes serialized arrays', function (): void {
        $meta = new PostMeta();
        $meta->setRawAttributes(['meta_value' => serialize(['foo' => 'bar'])]);

        expect($meta->value)->toBe(['foo' => 'bar']);
    });

    it('unserializes serialized integers', function (): void {
        $meta = new PostMeta();
        $meta->setRawAttributes(['meta_value' => 'i:42;']);

        expect($meta->value)->toBe(42);
    });

    it('returns the raw string when unserialize evaluates to false', function (): void {
        $meta = new PostMeta();
        $meta->setRawAttributes(['meta_value' => 'b:0;']);

        expect($meta->value)->toBe('b:0;');
    });

    it('unserializes NULL values', function (): void {
        $meta = new PostMeta();
        $meta->setRawAttributes(['meta_value' => 'N;']);

        expect($meta->value)->toBeNull();
    });
});
