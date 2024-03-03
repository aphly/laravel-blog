<?php

namespace Aphly\LaravelBlog\Controllers\Front;

use Aphly\Laravel\Models\Comm;
use Aphly\Laravel\Models\Config;
use Aphly\Laravel\Models\Dict;
use Aphly\Laravel\Models\UploadFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class Controller extends \Aphly\Laravel\Controllers\Controller
{
    public $user = null;

    public $config = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $auth = Auth::guard('user');
            $this->config = (new Config)->getByType();
            View::share("config",$this->config);
            if($auth->check()){
                $this->user = $auth->user();
                $this->user->avatar_src = UploadFile::getPath($this->user->avatar,$this->user->remote);
                View::share("user",$this->user);
            }else{
                View::share("user",[]);
            }
            View::share("dict",(new Dict)->getByKey());

            $this->afterController();

            return $next($request);
        });
    }

    public function afterController()
    {
        $class = (new Comm)->moduleClass();
        foreach ($class as $val) {
            if ($val!='Aphly\LaravelBlog' && $val!='Aphly\LaravelAdmin'
                && class_exists($val.'\Controllers\Front\Controller')) {
                $object = new ($val.'\Controllers\Front\Controller');
                if (method_exists($object, 'afterController')) {
                    $object->afterController($this);
                }
            }
        }
    }
}
