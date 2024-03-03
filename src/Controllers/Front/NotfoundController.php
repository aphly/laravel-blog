<?php

namespace Aphly\LaravelBlog\Controllers\Front;

use Illuminate\Http\Request;

class NotfoundController extends Controller
{
    function index(Request $request){
        $res['title'] = '404';
        return $this->makeView('laravel-blog-front::common.notfound',['res'=>$res]);
    }

}
