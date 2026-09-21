<?php

declare(strict_types=1);

namespace Sloth\Tests\Unit\Console\Commands;

use Sloth\Console\Commands\Make\MakeMenuCommand;
use Sloth\Core\Application;

/**
 * Unit tests for the make:menu command.
 */
describe('MakeMenuCommand', function (): void {
    it('creates a Menu model in the Menu directory', function (): void {
        $tmpDir = sys_get_temp_dir() . '/sloth_make_menu_' . uniqid();
        mkdir($tmpDir);

        $app = makeTestApp();
        Application::setInstance($app);
        $app->basePath = $tmpDir;

        $command = new MakeMenuCommand();
        $command->setLaravel($app);
        $command->setInput(new \Symfony\Component\Console\Input\ArrayInput(['name' => 'Primary']));
        $command->run(new \Symfony\Component\Console\Input\ArrayInput(['name' => 'Primary']), new \Symfony\Component\Console\Output\BufferedOutput());

        $path = $tmpDir . '/Menu/PrimaryMenu.php';
        expect(file_exists($path))->toBeTrue();

        $contents = file_get_contents($path);
        expect($contents)->toContain('class PrimaryMenu extends Menu');
        expect($contents)->toContain('use Sloth\Model\Menu;');
    });

    it('adds the Menu suffix without doubling it', function (): void {
        $app = makeTestApp();
        Application::setInstance($app);

        $command = new MakeMenuCommand();
        $command->setLaravel($app);

        $reflection = new \ReflectionMethod($command, 'resolveClass');
        expect($reflection->invoke($command, 'HeaderMenu'))->toBe('HeaderMenu');
        expect($reflection->invoke($command, 'Header'))->toBe('HeaderMenu');
    });

    it('derives location and name from the class name', function (): void {
        $tmpDir = sys_get_temp_dir() . '/sloth_make_menu_' . uniqid();
        mkdir($tmpDir);

        $app = makeTestApp();
        Application::setInstance($app);
        $app->basePath = $tmpDir;

        $command = new MakeMenuCommand();
        $command->setLaravel($app);

        $reflection = new \ReflectionMethod($command, 'replaceStub');
        $contents = $reflection->invoke($command, '{{ location }}|{{ name }}', 'FooterNav');

        expect($contents)->toContain('footer-nav');
        expect($contents)->toContain('Footer Nav');
    });

    it('refuses to overwrite an existing Menu', function (): void {
        $tmpDir = sys_get_temp_dir() . '/sloth_make_menu_' . uniqid();
        mkdir($tmpDir . '/Menu', 0o755, true);
        file_put_contents($tmpDir . '/Menu/PrimaryMenu.php', 'existing');

        $app = makeTestApp();
        Application::setInstance($app);
        $app->basePath = $tmpDir;

        $command = new MakeMenuCommand();
        $command->setLaravel($app);
        $command->setInput(new \Symfony\Component\Console\Input\ArrayInput(['name' => 'Primary']));

        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $status = $command->run(
            new \Symfony\Component\Console\Input\ArrayInput(['name' => 'Primary']),
            $output,
        );

        expect($status)->toBe(1);
        expect(file_get_contents($tmpDir . '/Menu/PrimaryMenu.php'))->toBe('existing');
    });
});