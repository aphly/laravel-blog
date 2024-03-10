<?php

namespace Aphly\LaravelBlog\Models;

use Aphly\Laravel\Models\Manager;
use Aphly\Laravel\Models\Menu;
use Aphly\Laravel\Models\Module as Module_base;
use Illuminate\Support\Facades\DB;

class Module extends Module_base
{
    public $dir = __DIR__;

    function remoteInstall($module_id)
    {
        $manager = Manager::where('username','admin')->firstOrError();

        $menu = Menu::create(['name' => 'Blog管理','route' =>'','pid'=>0,'uuid'=>$manager->uuid,'type'=>1,'module_id'=>$module_id,'sort'=>20]);
        if($menu){
            $menu2 = Menu::create(['name' => '文章管理','route' =>'','pid'=>$menu->id,'uuid'=>$manager->uuid,'type'=>1,'module_id'=>$module_id,'sort'=>0]);
            if($menu2){
                $data=[];
                $data[] =['name' => '文章分类','route' =>'blog_admin/article_category/index','pid'=>$menu2->id,'uuid'=>$manager->uuid,'type'=>2,'module_id'=>$module_id,'sort'=>2];
                $data[] =['name' => '文章列表','route' =>'blog_admin/article/index','pid'=>$menu2->id,'uuid'=>$manager->uuid,'type'=>2,'module_id'=>$module_id,'sort'=>1];
                DB::table('admin_menu')->insert($data);
            }
            Menu::create(['name' => '订阅','route' =>'blog_admin/subscribe/index','pid'=>$menu->id,'uuid'=>$manager->uuid,'type'=>2,'module_id'=>$module_id,'sort'=>0]);
        }
        $menuData = Menu::where(['module_id'=>$module_id])->get();
        $data=[];
        foreach ($menuData as $val){
            $data[] =['role_id' => 1,'menu_id'=>$val->id];
        }
        DB::table('admin_role_menu')->insert($data);

    }

    function remoteUninstall($module_id)
    {

    }



}
