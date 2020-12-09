<?php

namespace Sloth\Core;

use function array_key_exists;
use function get_class;
use Illuminate\Container\Container;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Log\LogServiceProvider;
use function method_exists;
use Sloth\Route\RouteServiceProvider;
use function str_replace;

class Application extends Container
{
    /**
     * Application version.
     *
     * @var string
     */
    const VERSION = '0.0.6';

    /**
     * The loaded service providers.
     *
     * @var array
     */
    protected $loadedProviders = [];

    /**
     * Project paths.
     * Same as $GLOBALS['sloth.paths'].
     *
     * @var array
     */
    protected $paths = [];

    /**
     * Create a new sloth application instance.
     *
     * @param string|null $basePath
     */
    public function __construct($basePath = null)
    {
        $this->registerBasebindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
    }

    /**
     * adds a filepath to our container
     *
     * @param $key
     * @param $path
     */
    public function addPath($key, $path)
    {
        $this->instance('path.' . $key, $path);
    }

    /**
     * @param mixed $name
     * @param mixed $data
     * @param mixed $options
     */
    public function callModule($name, $data = [], $options = [])
    {
        $module_name = 'Theme\Module\\' . \Cake\Utility\Inflector::camelize(str_replace(
            '-',
            '_',
            $name
        )) . 'Module';
        $myModule    = new $module_name($options);
        foreach ($data as $k => $v) {
            $myModule->set($k, $v);
        }

        return $myModule->render();
    }

    /**
     * @TODO: That's incredibly hackish! Look at Illuminate\Container\Container::resolve
     *
     * @param string $abstract
     * @param array  $parameters
     *
     * @return mixed
     */
    public function makeWith($abstract, array $parameters = [])
    {
        if ($abstract == 'Illuminate\Pagination\LengthAwarePaginator') {
            $abstract = 'Sloth\Pagination\Paginator';
        }

        return parent::makeWith($abstract, $parameters); // TODO: Change the autogenerated stub
    }

    /**
     * Register a service provider with the application.
     *
     * @param \Sloth\Core\ServiceProvider|string $provider
     * @param array                              $options
     * @param bool                               $force
     *
     * @return \Sloth\Core\ServiceProvider
     */
    public function register($provider, array $options = [], $force = false)
    {
        if (! $provider instanceof ServiceProvider) {
            $provider = new $provider($this);
        }
        if (array_key_exists($providerName = get_class($provider), $this->loadedProviders)) {
            return;
        }

        $this->loadedProviders[$providerName] = true;
        $provider->register();

        if (method_exists($provider, 'boot')) {
            $provider->boot();
        }
    }


    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Register basic bindings into the container.
     */
    protected function registerBasebindings()
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance(Container::class, $this);
    }

    /**
     * Register base service providers.
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new EventServiceProvider($this));
        $this->register(new LogServiceProvider($this));
        $this->register(new RouteServiceProvider($this));
    }

    /**
     * Register the core class aliases in the container.
     */
    protected function registerCoreContainerAliases()
    {
        $list = [
            'app'    => [
                \Sloth\Core\Application::class,
                \Illuminate\Contracts\Container\Container::class,
                \Illuminate\Contracts\Foundation\Application::class,
                \Psr\Container\ContainerInterface::class,
            ],
            'events' => [
                \Illuminate\Events\Dispatcher::class,
                \Illuminate\Contracts\Events\Dispatcher::class,
            ],
        ];

        foreach ($list as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }
}
