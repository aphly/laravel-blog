<?php

namespace Aphly\LaravelBlog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Aphly\Laravel\Models\Model;

class Subscribe extends Model
{
    use HasFactory;
    protected $table = 'blog_subscribe';
    protected $primaryKey = 'id';

    //public $timestamps = false;

    protected $fillable = [
        'email','status'
    ];


}
