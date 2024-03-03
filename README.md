**laravel Blog**<br>

包含 user <br>

环境<br>
php8.1+<br>
laravel10.0+<br>
mysql5.7+<br>

安装<br>
`composer require aphly/laravel-blog` <br>
`php artisan vendor:publish --provider="Aphly\LaravelBlog\BlogServiceProvider"` <br>

1、config/auth.php<br>
数组guards中 添加<br>
`'user' => [
'driver' => 'session',
'provider' => 'user'
]`
<br>数组providers中 添加<br>
`'user' => [
'driver' => 'eloquent',
'model' => Aphly\LaravelBlog\Models\User::class
]`
