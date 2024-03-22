<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::middleware(['web'])->group(function () {
    //Home
    Route::get('/', 'Aphly\LaravelBlog\Controllers\Front\ArticleController@index');

    //Subscribe
    Route::post('subscribe/ajax', 'Aphly\LaravelBlog\Controllers\Front\SubscribeController@ajax');

    //article
    Route::match(['get'],'article/{id}', 'Aphly\LaravelBlog\Controllers\Front\ArticleController@detail')->where('id', '[0-9]+');
    Route::match(['get'],'article/index', 'Aphly\LaravelBlog\Controllers\Front\ArticleController@index');
    Route::match(['get'],'article/category', 'Aphly\LaravelBlog\Controllers\Front\ArticleController@category');

    Route::prefix('account')->group(function () {
        Route::middleware(['userAuth'])->group(function () {
            Route::match(['get', 'post'],'subscribe', 'Aphly\LaravelBlog\Controllers\Front\SubscribeController@index');
        });
    });

});

Route::middleware(['web'])->group(function () {

    Route::prefix('blog_admin')->middleware(['managerAuth'])->group(function () {

        Route::middleware(['rbac'])->group(function () {

            $route_arr = [
                ['article','\ArticleController'],['subscribe','\SubscribeController']
            ];

            foreach ($route_arr as $val){
                Route::get($val[0].'/index', 'Aphly\LaravelBlog\Controllers\Admin'.$val[1].'@index');
                Route::get($val[0].'/form', 'Aphly\LaravelBlog\Controllers\Admin'.$val[1].'@form');
                Route::post($val[0].'/save', 'Aphly\LaravelBlog\Controllers\Admin'.$val[1].'@save');
                Route::post($val[0].'/del', 'Aphly\LaravelBlog\Controllers\Admin'.$val[1].'@del');
            }

            Route::get('article_category/tree', 'Aphly\LaravelBlog\Controllers\Admin\ArticleCategoryController@tree');
            Route::match(['post'],'article/img', 'Aphly\LaravelBlog\Controllers\Admin\ArticleController@uploadImg');

            $route_arr = [
                ['article_category','\ArticleCategoryController']
            ];
            Route::get('article_category/rebuild', 'Aphly\LaravelBlog\Controllers\Admin\ArticleCategoryController@rebuild');

            foreach ($route_arr as $val){
                Route::get($val[0].'/index', 'Aphly\LaravelBlog\Controllers\Admin'.$val[1].'@index');
                Route::match(['get', 'post'],$val[0].'/add', 'Aphly\LaravelBlog\Controllers\Admin'.$val[1].'@add');
                Route::match(['get', 'post'],$val[0].'/edit', 'Aphly\LaravelBlog\Controllers\Admin'.$val[1].'@edit');
                Route::post($val[0].'/del', 'Aphly\LaravelBlog\Controllers\Admin'.$val[1].'@del');
            }
        });
    });

});
