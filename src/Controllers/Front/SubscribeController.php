<?php

namespace Aphly\LaravelBlog\Controllers\Front;

use Aphly\Laravel\Exceptions\ApiException;
use Aphly\LaravelBlog\Models\Subscribe;
use Aphly\Laravel\Models\User;
use Illuminate\Http\Request;

class SubscribeController extends Controller
{
    public function index(Request $request)
    {
        $email = User::initId();
        $res['info'] = Subscribe::where(['email'=>$email])->first();
        if($request->isMethod('post')){
            $status = $request->input('status',1);
            if(!empty($res['info'])){
                $res['info']->status = $status;
                $res['info']->save();
            }else{
                Subscribe::create(['email'=>$email,'status'=>$status]);
            }
            throw new ApiException(['code'=>0,'msg'=>'success']);
        }else{
            $res['title'] = 'Subscribe';
            return $this->makeView('laravel-front::account.subscribe',['res'=>$res]);
        }
    }

    public function ajax(Request $request)
    {
        $input = $request->all();
        if(filter_var($input['email'], FILTER_VALIDATE_EMAIL)){
            $res['info'] = Subscribe::where('email',$input['email'])->first();
            if(empty($res['info'])){
                Subscribe::create($input);
            }
            throw new ApiException(['code'=>0,'msg'=>'success']);
        }else{
            throw new ApiException(['code'=>1,'msg'=>'Email Error']);
        }
    }


}
