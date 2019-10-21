<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/访问方式', '文件夹名\控制器名@方法名');
Route::get('/index', 'Index\IndexController@index');   //首页

Route::get('/weChat', 'User\UserController@weChat');   //微信登陆

Route::get('/goodsInfo', 'Goods\GoodsController@goodsInfo');    //获取商品详情信息

Route::get('/type_shop', 'Goods\GoodsController@type_shop');     //根据导航栏子级分类获取店铺

Route::get('/father_type_shop', 'Goods\GoodsController@father_type_shop');      //根据导航栏父级分类获取子级分类及店铺信息
