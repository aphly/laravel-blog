<?php

namespace Aphly\LaravelBlog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Aphly\Laravel\Models\Model;

class Article extends Model
{
    use HasFactory;
    protected $table = 'blog_article';
    //public $timestamps = false;
    protected $fillable = [
        'title','content','viewed','status','article_category_id'
    ];

    function category(){
        return $this->hasOne(ArticleCategory::class,'id','article_category_id');
    }

}
