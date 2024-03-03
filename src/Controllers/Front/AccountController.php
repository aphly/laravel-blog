<?php

namespace Aphly\LaravelBlog\Controllers\Front;

use Aphly\Laravel\Exceptions\ApiException;
use Aphly\Laravel\Libs\Helper;
use Aphly\Laravel\Libs\Seccode;
use Aphly\Laravel\Models\UploadFile;

use Aphly\LaravelBlog\Models\RemoteEmail;
use Aphly\LaravelBlog\Mail\Forget;
use Aphly\LaravelBlog\Mail\Verify;
use Aphly\LaravelBlog\Models\User;
use Aphly\LaravelBlog\Models\UserAuth;
use Aphly\LaravelBlog\Requests\AccountRequest;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use function config;
use function redirect;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        if($request->isMethod('post')){
            $user = User::where(['nickname'=>$request->input('nickname')])->first();
            if(!empty($user) && ($user->uuid!=$this->user->uuid)){
                throw new ApiException(['code'=>1,'msg'=>'nickname already exists']);
            }else{
                $image = false;
                $oldImage = '';
                $oldRemote = $this->user->remote;
                if($request->hasFile('image')){
                    $UploadFile = new UploadFile(1);
                    $this->user->remote = $UploadFile->isRemote();
                    $image = $UploadFile->upload($request->file('image'),'public/account');
                    if ($image) {
                        $oldImage = $this->user->avatar;
                        $this->user->avatar = $image;
                    }
                }
                $this->user->nickname = $request->input('nickname');
                unset($this->user->avatar_src);
                if ($this->user->save()) {
                    if($image){
                        (new UploadFile)->del($oldImage,$oldRemote);
                    }
                    throw new ApiException(['code'=>0,'msg'=>'success']);
                } else {
                    throw new ApiException(['code'=>1,'msg'=>'upload error']);
                }
            }
        }else{
            $res['title'] = 'Account index';
            return $this->makeView('laravel-blog-front::account.index',['res'=>$res]);
        }
    }

    public function autoLogin(Request $request)
    {
        try {
            $decrypted = Crypt::decryptString($request->token);
            $user = User::where('token',$decrypted)->first();
            if(!empty($user)){
                Auth::guard('user')->login($user);
                return redirect('/');
            }else{
                throw new ApiException(['code'=>2,'msg'=>'No user']);
            }
        } catch (DecryptException $e) {
            throw new ApiException(['code'=>1,'msg'=>'Token_error']);
        }
    }


    public function login(AccountRequest $request)
    {
        $key = 'user_login_'.$request->ip();
        if($request->isMethod('post')) {
            $arr['id'] = $request->input('id');
            $id_type = $request->input('id_type');
            if(in_array($id_type,UserAuth::$id_type)){
                $arr['id_type'] = $id_type;
            }else{
                throw new ApiException(['code'=>1,'msg'=>'Id_type Err','data'=>['code'=>['Id_type Err']]]);
            }
            $userAuth = UserAuth::where($arr)->first();
            if(!empty($userAuth)){
                if($this->limiter($key,5)){
                    if(config('base.seccode_login')==1 || (config('base.seccode_login')==2 && $this->limiter($key))){
                        if(!((new Seccode())->check($request->input('code')))){
                            throw new ApiException(['code'=>11000,'msg'=>'Incorrect Code','data'=>['code'=>['Incorrect Code']]]);
                        }
                    }
                    if(Hash::check($request->input('password',''),$userAuth->password)){
                        $user = User::where(['uuid'=>$userAuth->uuid])->firstOrError();
                        $userAuth->update(['last_time'=>time(),'last_ip'=>$request->ip(),'user_agent' => $request->header('user-agent'),'accept_language' => $request->header('accept-language')]);
                        $user->generateToken();
                        Auth::guard('user')->login($user);
                        $user->afterLogin();
                        $user->id_type = $userAuth->id_type;
                        $user->id = $userAuth->id;
                        throw new ApiException(['code'=>0,'msg'=>'login success','data'=>['redirect'=>$user->redirect(),'user'=>$user]]);
                    }else{
                        $this->limiterIncrement($key,15*60);
                        if($this->limiter($key)==1){
                            throw new ApiException(['code'=>2,'msg'=>'Incorrect '.$arr['id_type'].' or password']);
                        }
                    }
                }else{
                    throw new ApiException(['code'=>11000,'msg'=>'Too many errors, locked out for 15 minutes','data'=>['password'=>['Too many errors, locked out for 15 minutes']]]);
                }
            }
            throw new ApiException(['code'=>11000,'msg'=>'Incorrect '.$arr['id_type'].' or password','data'=>['password'=>['Incorrect '.$arr['id_type'].' or password']]]);
        }else{
            $res['title'] = 'Login';
            $res['seccode'] = $this->limiter($key);
            return $this->makeView('laravel-blog-front::account.login',['res'=>$res]);
        }
    }

    public function register(AccountRequest $request)
    {
        $key = 'user_register_'.$request->ip();
        if($request->isMethod('post')) {
            if (config('base.seccode_register')==1) {
                if (!((new Seccode())->check($request->input('code')))) {
                    throw new ApiException(['code' => 11000, 'msg' => 'Incorrect Code', 'data' => ['code' => ['Incorrect Code']]]);
                }
            }
            if($this->limiter($key,1)) {
                $post = $request->all();
                if(!in_array($post['id_type'],UserAuth::$id_type)){
                    throw new ApiException(['code'=>1,'msg'=>'Id_type Err','data'=>['code'=>['Id_type Err']]]);
                }
                $post['uuid'] = Helper::uuid();
                $post['password'] = Hash::make($post['password']);
                $post['last_ip'] = $request->ip();
                $post['last_time'] = time();
                $post['user_agent'] = $request->header('user-agent');
                $post['accept_language'] = $request->header('accept-language');
                $userAuth = UserAuth::create($post);
                if ($userAuth->uuid) {
                    $user = User::create([
                        'nickname' => str::random(8),
                        'uuid' => $userAuth->uuid,
                        'token' => Str::random(64),
                        'token_expire' => time() + 120 * 60,
                    ]);
                    Auth::guard('user')->login($user);
                    $user->afterRegister();
                    $user->id_type = $userAuth->id_type;
                    $user->id = $userAuth->id;
                    if ($userAuth->id_type == 'email' && config('blog.email_verify')) {
                        (new RemoteEmail())->send([
                            'email'=>$userAuth->id,
                            'title'=>'Account Email Verify',
                            'content'=>(new Verify($userAuth))->render(),
                            'type'=>config('blog.email_type'),
                            'queue_priority'=>1,
                            'is_cc'=>0
                        ]);
                    }
                    $this->limiterIncrement($key,15*60);
                    throw new ApiException(['code' => 0, 'msg' => 'Register success', 'data' => ['redirect' => $user->redirect(), 'user' => $user]]);
                } else {
                    throw new ApiException(['code' => 1, 'msg' => 'Register fail']);
                }
            }else{
                throw new ApiException(['code' => 2, 'msg' => 'Registration is too frequent, please wait 15 minutes']);
            }
        }else{
            $res['title'] = 'Register';
            $res['seccode'] = $this->limiter($key);
            return $this->makeView('laravel-blog-front::account.register',['res'=>$res]);
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('user')->logout();
        throw new ApiException(['code'=>0,'msg'=>'logout success','data'=>['redirect'=>'/']]);
    }

    public function emailVerify(Request $request)
    {
        $res['title'] = 'Confirm Email';
        return $this->makeView('laravel-blog-front::account.email_verify',['res'=>$res]);
    }

    public function emailVerifySend(Request $request)
    {
        $user = Auth::guard('user')->user();
        if($user){
            $key = 'email_'.$request->ip();
            if($this->limiter($key,1)) {
                $userauth = UserAuth::where(['id_type' => 'email', 'uuid' => $user->uuid])->first();
                if (!empty($userauth)) {
                    //(new MailSend())->do($userauth->id, new Verify($userauth),'email_vip');
                    (new RemoteEmail)->send([
                        'email'=>$userauth->id,
                        'title'=>'Account Email Verify',
                        'content'=>(new Verify($userauth))->render(),
                        'type'=>config('blog.email_type'),
                        'queue_priority'=>1,
                        'is_cc'=>0
                    ]);
                    $this->limiterIncrement($key,2*60);
                    throw new ApiException(['code' => 0, 'msg' => 'Email has been sent', 'data' => ['redirect' => '/']]);
                }else{
                    throw new ApiException(['code' => 1, 'msg' => 'Email not exist', 'data' => ['redirect' => '/']]);
                }
            }else{
                throw new ApiException(['code'=>2,'msg'=>'Email delivery lock 2 minutes','data'=>['redirect'=>'/']]);
            }
        }else{
            throw new ApiException(['code'=>3,'msg'=>'user error','data'=>['redirect'=>'/']]);
        }
    }

    public function emailVerifyCheck(Request $request)
    {
        $res['title'] = 'Account Email Check';
        try {
            $decrypted = Crypt::decryptString($request->token);
            $decrypted = explode(',',$decrypted);
            $uuid = $decrypted[0]??0;
            $time = $decrypted[1]??0;
            if($uuid && $time>=time()) {
                $userAuth = UserAuth::where(['id_type'=>'email','uuid'=>$uuid])->first();
                if(!empty($userAuth)){
                    $userAuth->update(['verified'=>1]);
                    $res['msg'] = 'Email activation succeeded';
                }else{
                    $res['msg'] = 'User not found';
                }
            }else{
                $res['msg'] =  'Token Expired';
            }
        } catch (DecryptException $e) {
            $res['msg'] =  'Token Error';
        }
        return $this->makeView('laravel-blog-front::account.email_check',['res'=>$res]);
    }

    public function forget(AccountRequest $request)
    {
        if($request->isMethod('post')) {
            if (config('base.seccode_forget')==1) {
                if (!((new Seccode())->check($request->input('code')))) {
                    throw new ApiException(['code' => 11000, 'msg' => 'Incorrect Code', 'data' => ['code' => ['Incorrect Code']]]);
                }
            }
            $userauth = UserAuth::where(['id_type'=>'email','id'=>$request->input('id')])->first();
            if(!empty($userauth)){
                //(new MailSend())->do($userauth->id,new Forget($userauth),'email_vip');
                (new RemoteEmail())->send([
                    'email'=>$userauth->id,
                    'title'=>'Password Reset',
                    'content'=>(new Forget($userauth))->render(),
                    'type'=>config('blog.email_type'),
                    'queue_priority'=>1,
                    'is_cc'=>0
                ]);
                throw new ApiException(['code'=>0,'msg'=>'Email has been sent','data'=>['redirect'=>'/account/forget/confirmation']]);
            }else{
                throw new ApiException(['code'=>11000,'msg'=>'Email not exist','data'=>['id'=>['email not exist']]]);
            }
        }else{
            $res['title'] = 'Forget your password';
            return $this->makeView('laravel-blog-front::account.forget',['res'=>$res]);
        }
    }

    public function forgetConfirmation(Request $request)
    {
        $res['title'] = 'Forget password confirmation';
        return $this->makeView('laravel-blog-front::account.forget_confirmation',['res'=>$res]);
    }

    public function forgetPassword(AccountRequest $request)
    {
        try {
            $decrypted = Crypt::decryptString($request->token);
            $decrypted = explode(',',$decrypted);
            $uuid = $decrypted[0]??0;
            $time = $decrypted[1]??0;
            if($uuid && $time>=time()) {
                $userAuth = UserAuth::where(['id_type'=>'email','uuid'=>$uuid])->first();
                if(!empty($userAuth)){
                    if($request->isMethod('post')) {
                        $userAuth->changePassword($userAuth->uuid,$request->input('password'));
                        Auth::guard('user')->logout();
                        throw new ApiException(['code'=>0,'msg'=>'Password reset success','data'=>['redirect'=>route('login')]]);
                    }else{
                        $res['title'] = 'Reset Password';
                        $res['token'] = $request->token;
                        $res['userAuth'] = $userAuth;
                        return $this->makeView('laravel-blog-front::account.forget-password', ['res' => $res]);
                    }
                }else{
                    throw new ApiException(['code'=>3,'msg'=>'User error','data'=>['redirect'=>route('login')]]);
                }
            }else{
                throw new ApiException(['code'=>2,'msg'=>'Token Expire','data'=>['redirect'=>route('login')]]);
            }
        } catch (DecryptException $e) {
            throw new ApiException(['code'=>1,'msg'=>'Token Error','data'=>['redirect'=>route('login')]]);
        }
    }

    public function blocked(Request $request)
    {
        $res['title'] = 'Account Blocked';
        return $this->makeView('laravel-blog-front::account.blocked',['res'=>$res]);
    }


}
