<?php

namespace Aphly\LaravelBlog;

use Aphly\Laravel\Models\Comm;
use Aphly\Laravel\Providers\ServiceProvider;
use Aphly\LaravelBlog\Middleware\UserAuth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */

    public function register()
    {
		$this->mergeConfigFrom(
            __DIR__.'/config/blog.php', 'blog'
        );
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
            $this->publishes([
                __DIR__ . '/config/blog.php' => config_path('blog.php'),
                __DIR__ . '/public' => public_path('static/blog')
            ]);
            //$this->loadMigrationsFrom(__DIR__.'/migrations');
            $this->loadViewsFrom(__DIR__ . '/views', 'laravel-blog');
            $this->loadViewsFrom(__DIR__ . '/views/front', 'laravel-blog-front');
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
            $this->addRouteMiddleware('userAuth', UserAuth::class);

        }
    }



}
