<?php

namespace Sloth\Deployment;

final class Deployment
{
    protected $hooks = [
        'edited_terms',
        'created_term',
        'post_updated',
        'acf/save_post',
    ];
    /**
     * Sloth\Deployment instance.
     *
     * @var \Sloth\Deployment\Deployment
     */
    protected static $instance = null;

    /**
     * Add required hooks to WordPress
     */
    public function boot()
    {
        if (getenv('SLOTH_DEPLOYMENT_WEBHOOK')) {
            foreach ($this->hooks as $hook) {
                add_action($hook, [$this, 'trigger']);
            }
        }
    }

    /**
     * Retrieve Sloth class instance.
     *
     * @return \Sloth\Deployment\Deployment
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * trigger the deployment
     */
    public function trigger()
    {
        if ($hook = getenv('SLOTH_DEPLOYMENT_WEBHOOK')) {
            wp_remote_post($hook);
        }
    }
}
