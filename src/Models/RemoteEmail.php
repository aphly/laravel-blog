<?php

namespace Aphly\LaravelBlog\Models;

use Aphly\Laravel\Mail\MailSend;

class RemoteEmail
{
    function send($input){
        $MailSend = new MailSend();
        $MailSend->appid = config('blog.email_appid');
        $MailSend->secret = config('blog.email_secret');
        return $MailSend->remote($input);
    }
}
