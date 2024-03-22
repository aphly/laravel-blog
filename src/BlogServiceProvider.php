<?php

namespace Aphly\LaravelBlog;

use Aphly\Laravel\Models\Comm;
use Aphly\Laravel\Providers\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $comm_module= (new Comm)->moduleClass();
        if(in_array('Aphly\LaravelBlog',$comm_module)) {
            //$this->loadMigrationsFrom(__DIR__.'/migrations');
            $this->loadViewsFrom(__DIR__ . '/views', 'laravel-blog');
            $this->loadViewsFrom(__DIR__ . '/views/front', 'laravel-front');
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        }
    }



}
