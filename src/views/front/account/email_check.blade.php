@include('laravel-blog-front::common.header')
<link rel="stylesheet" href="{{ URL::asset('static/blog/css/account.css') }}">

<div class=" container">
    <div class="account_msg">
        <p>{{$res['msg']}}</p>
    </div>
</div>


@include('laravel-blog-front::common.footer')
