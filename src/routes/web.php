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

    //oauth
    Route::get('oauth/{service}','Aphly\LaravelBlog\Controllers\Front\OAuthController@redirectToProvider')->name('oauth');
    Route::get('oauth/{service}/callback','Aphly\LaravelBlog\Controllers\Front\OAuthController@handleProviderCallback')->name('oauthCallback');

    //Subscribe
    Route::post('subscribe/ajax', 'Aphly\LaravelBlog\Controllers\Front\SubscribeController@ajax');

    //article
    Route::match(['get'],'article/{id}', 'Aphly\LaravelBlog\Controllers\Front\ArticleController@detail')->where('id', '[0-9]+');
    Route::match(['get'],'article/index', 'Aphly\LaravelBlog\Controllers\Front\ArticleController@index');
    Route::match(['get'],'article/category', 'Aphly\LaravelBlog\Controllers\Front\ArticleController@category');

    //404
    Route::get('404', 'Aphly\LaravelBlog\Controllers\Front\NotfoundController@index');

    Route::prefix('account')->group(function () {
        Route::match(['get'],'autologin/{token}', 'Aphly\LaravelBlog\Controllers\Front\AccountController@autoLogin');

        Route::match(['get'],'blocked', 'Aphly\LaravelBlog\Controllers\Front\AccountController@blocked')->name('blocked');
        Route::match(['get'],'email-verify', 'Aphly\LaravelBlog\Controllers\Front\AccountController@emailVerify')->name('emailVerify');
        Route::match(['get'],'email-verify/send', 'Aphly\LaravelBlog\Controllers\Front\AccountController@emailVerifySend');
        Route::get('email-verify/{token}', 'Aphly\LaravelBlog\Controllers\Front\AccountController@emailVerifyCheck');

        Route::match(['get', 'post'],'forget', 'Aphly\LaravelBlog\Controllers\Front\AccountController@forget');
        Route::match(['get'],'forget/confirmation', 'Aphly\LaravelBlog\Controllers\Front\AccountController@forgetConfirmation');
        Route::match(['get', 'post'],'forget-password/{token}', 'Aphly\LaravelBlog\Controllers\Front\AccountController@forgetPassword');

        Route::get('logout', 'Aphly\LaravelBlog\Controllers\Front\AccountController@logout');

        Route::middleware(['userAuth'])->group(function () {
            Route::match(['get', 'post'],'register', 'Aphly\LaravelBlog\Controllers\Front\AccountController@register')->name('register');
            Route::match(['get', 'post'],'login', 'Aphly\LaravelBlog\Controllers\Front\AccountController@login')->name('login');

            Route::match(['get', 'post'],'index', 'Aphly\LaravelBlog\Controllers\Front\AccountController@index');
            Route::match(['get', 'post'],'subscribe', 'Aphly\LaravelBlog\Controllers\Front\SubscribeController@index');

        });
    });

});

Route::middleware(['web'])->group(function () {

    Route::prefix('blog_admin')->middleware(['managerAuth'])->group(function () {

        Route::middleware(['rbac'])->group(function () {

            Route::get('user/index', 'Aphly\LaravelBlog\Controllers\Admin\UserController@index');
            Route::match(['get', 'post'],'user/{uuid}/edit', 'Aphly\LaravelBlog\Controllers\Admin\UserController@edit')->where('uuid', '[0-9]+');
            Route::match(['get', 'post'],'user/{uuid}/password', 'Aphly\LaravelBlog\Controllers\Admin\UserController@password')->where('uuid', '[0-9]+');
            Route::post('user/del', 'Aphly\LaravelBlog\Controllers\Admin\UserController@del');
            Route::match(['get', 'post'],'user/{uuid}/credit', 'Aphly\LaravelBlog\Controllers\Admin\UserController@credit')->where('uuid', '[0-9]+');
            Route::match(['get', 'post'],'user/{uuid}/avatar', 'Aphly\LaravelBlog\Controllers\Admin\UserController@avatar')->where('uuid', '[0-9]+');
            Route::match(['get', 'post'],'user/{uuid}/verify', 'Aphly\LaravelBlog\Controllers\Admin\UserController@verify')->where('uuid', '[0-9]+');

            $route_arr = [
                ['article','\ArticleController'],['subscribe','\SubscribeController']
            ];

            foreach ($route_arr as $val){
                Route::get($val[0].'/index', 'Aphly\LaravelBlog\Controllers\Admin'.$val[1].'@index');
                Route::get($val[0].'/form', 'Aphly\LaravelBlog\Controllers\Admin'.$val[1].'@form');
                Route::post($val[0].'/save', 'Aphly\LaravelBlog\Controllers\Admin'.$val[1].'@save');
                Route::post($val[0].'/del', 'Aphly\LaravelBlog\Controllers\Admin'.$val[1].'@del');
            }

            Route::get('links/tree', 'Aphly\LaravelBlog\Controllers\Admin\LinksController@tree');
            Route::get('article_category/tree', 'Aphly\LaravelBlog\Controllers\Admin\ArticleCategoryController@tree');
            Route::match(['post'],'article/img', 'Aphly\LaravelBlog\Controllers\Admin\ArticleController@uploadImg');

            $route_arr = [
                ['article_category','\ArticleCategoryController'],['links','\LinksController']
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
