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

Route::get('/index_coupon', 'Index\IndexController@index_coupon');    //首页-优惠券

Route::get('/coupon_receive', 'Index\IndexController@coupon_receive');

Route::get('/assemble', 'Index\IndexController@assemble');   //拼团

Route::get('/limited_time', 'Index\IndexController@limited_time');    //首页-限时抢

Route::get('/shop_goods', 'Goods\GoodsController@shop_goods');   //根据店铺id 获取店铺下所有商品

Route::get('/weChat', 'User\UserController@weChat');   //微信登陆

Route::get('/shop_settled', 'User\UserController@shop_settled');    //商家入驻

Route::get('/shop_type', 'User\UserController@shop_type');     //点击获取主营项目

Route::get('/accessToken', 'User\UserController@accessToken');   //accessToken

Route::post('/bankcard', 'User\UserController@bankcard');   //银行卡接口

Route::post('/upload', 'User\UserController@upload');   //图片上传（多图）

Route::get('/add_bankcard', 'User\UserController@add_bankcard');   //银行卡接口

Route::get('/bankcard_list', 'User\UserController@bankcard_list');   //银行卡列表

Route::get('/add_bankcard_delete', 'User\UserController@add_bankcard_delete');   //银行卡解绑（删除）

Route::get('/user_coupon', 'User\UserController@user_coupon');    //优惠券

Route::get('/couponlist', 'Goods\GoodsController@couponlist');     //优惠卷列表

Route::get('/goodsInfo', 'Goods\GoodsController@goodsInfo');    //获取商品详情信息

Route::get('/type_shop', 'Goods\GoodsController@type_shop');     //根据导航栏子级分类获取店铺

Route::get('/father_type_shop', 'Goods\GoodsController@father_type_shop');      //根据导航栏父级分类获取子级分类及店铺信息

Route::get('/add_cart', 'Goods\GoodsController@add_cart');      //点击商品加入购物车

Route::get('/cartList', 'Goods\GoodsController@cartList');      //购物车列表

Route::get('/cart_delete', 'Goods\GoodsController@cart_delete');    //购物车删除

Route::get('/collectionaddd', 'Goods\GoodsController@collectionaddd');     //查询店铺是否收藏

Route::get('/collectionshop', 'Goods\GoodsController@collectionshop');     //查询商品是否收藏

Route::get('/collectiondele', 'Goods\GoodsController@collectiondele');     //收藏商品删除

Route::get('/shop_collection_dele', 'Goods\GoodsController@shop_collection_dele');     //收藏店铺删除

Route::get('/add_collection', 'Goods\GoodsController@add_collection');     //商品加入收藏

Route::get('/collection_list', 'Goods\GoodsController@collection_list');      //商品收藏列表

Route::get('/shop_collection', 'Goods\GoodsController@shop_collection');      //店铺收藏

Route::get('/shop_collection_list', 'Goods\GoodsController@shop_collection_list');       //店铺收藏列表

Route::get('/add_order', 'Order\OrderController@add_order');      //生成订单

Route::get('/order_list', 'Order\OrderController@order_list');     //订单列表

Route::get('/order_status_list', 'Order\OrderController@order_status_list');     //根据订单状态获取订单信息

Route::get('/order_detail', 'Order\OrderController@order_detail');     //订单详情

Route::get('/user_Address', 'User\UserController@user_Address');      //添加用户地址

Route::get('/user_Address_list', 'User\UserController@user_Address_list');    //地址列表

Route::get('update_address', 'User\UserController@update_address');     //地址修改

Route::get('delete_address', 'User\UserController@delete_address');    //地址删除

Route::get('user_center', 'User\UserController@user_center');     //用户中心

Route::get('user_update', 'User\UserController@user_update');      //修改用户信息

Route::get('user_history', 'User\UserController@user_history');     //我的足迹

Route::get('user_sign', 'User\UserController@user_sign');    //签到

Route::get('/whole_shop', 'Goods\GoodsController@whole_shop');    //全部店铺

Route::get('/nearby_shop', 'Goods\GoodsController@nearby_shop');   //附近店铺

//Route::get('/displace', 'Goods\GoodsController@displace');   //置换商城

Route::get('/subscribe', 'Goods\GoodsController@subscribe');   //预约




/*
 * 后台
 */
//登录接口
Route::any('user','Admin\Admin_loginController@user');
//用户列表
Route::any('userlist','Admin\Admin_loginController@userlist')->middleware('checkLogin');

Route::post('admin_list','Admin\Admin_loginController@admin_list')->middleware('checkLogin');
Route::post('admin_pwdUpdate','Admin\Admin_loginController@admin_pwdUpdate')->middleware('checkLogin');
//用户总人数
Route::any('userman','Admin\Admin_loginController@userman')->middleware('checkLogin');
//总分享币
Route::any('usermoney','Admin\Admin_loginController@usermoney')->middleware('checkLogin');
//总积分
Route::any('userintegral','Admin\Admin_loginController@userintegral')->middleware('checkLogin');
//用户分享币修改
Route::any('money','Admin\Admin_loginController@money')->middleware('checkLogin');
//用户积分修改
Route::any('integral','Admin\Admin_loginController@integral')->middleware('checkLogin');
//用户搜索
Route::any('search','Admin\Admin_loginController@search')->middleware('checkLogin');
//用户移除
Route::any('userdelete','Admin\Admin_loginController@userdelete')->middleware('checkLogin');
//用户拉黑
Route::any('userblack','Admin\Admin_loginController@userblack')->middleware('checkLogin');
//商家移除
Route::any('businessdelete','Admin\Admin_loginController@businessdelete')->middleware('checkLogin');
//商家搜索
Route::any('serachbusiness','Admin\Admin_loginController@serachbusiness')->middleware('checkLogin');
//商家总人数
Route::any('businessman','Admin\Admin_loginController@businessman')->middleware('checkLogin');
//商家总营业额
Route::any('businessmoney','Admin\Admin_loginController@businessmoney')->middleware('checkLogin');
//商家总余额
Route::any('businessall','Admin\Admin_loginController@businessall')->middleware('checkLogin');
//商家拉黑
Route::any('businessblack','Admin\Admin_loginController@businessblack')->middleware('checkLogin');
//商家拉黑类表
Route::any('businessblacktype','Admin\Admin_loginController@businessblacktype')->middleware('checkLogin');
//商家从拉黑表单移除
Route::any('remove','Admin\Admin_loginController@remove')->middleware('checkLogin');
//后台商家展示
Route::any('business','Admin\Admin_loginController@business')->middleware('checkLogin');
//商家申请入驻
Route::any('settled','Admin\Admin_loginController@settled')->middleware('checkLogin');
//商家入驻待审核
Route::any('businesssettled','Admin\Admin_loginController@businesssettled')->middleware('checkLogin');
//审核商家
Route::any('examine','Admin\Admin_loginController@examine')->middleware('checkLogin');
//后台图片上传
Route::any('advertis_img','Admin\Admin_loginController@advertis_img')->middleware('checkLogin');
//首页轮播图
Route::any('recommend','Admin\Admin_loginController@recommend')->middleware('checkLogin');
//和首页轮播的推荐
Route::any('recommendrou','Admin\Admin_loginController@recommendrou')->middleware('checkLogin');
//图片修改
Route::any('rotationupdate','Admin\Admin_loginController@rotationupdate')->middleware('checkLogin');
//六条推荐位的修改
Route::any('modification','Admin\Admin_loginController@modification')->middleware('checkLogin');
//图片修改
Route::any('imguptate','Admin\Admin_loginController@imguptate')->middleware('checkLogin');
//数据修改
Route::any('yesorno','Admin\Admin_loginController@yesorno')->middleware('checkLogin');
//优惠卷商品和id添加
Route::any('coupon','Admin\Admin_loginController@coupon')->middleware('checkLogin');
//优惠卷展示
Route::any('couponexhibition','Admin\Admin_loginController@couponexhibition')->middleware('checkLogin');
//优惠卷删除
Route::any('coupontele','Admin\Admin_loginController@coupontele')->middleware('checkLogin');
//优惠卷搜索
Route::any('couponsearch','Admin\Admin_loginController@couponsearch')->middleware('checkLogin');
//优惠卷添加
Route::any('couponinsert','Admin\Admin_loginController@couponinsert')->middleware('checkLogin');
//店铺管理
Route::post('admin_shop','Admin\Admin_loginController@admin_shop')->middleware('checkLogin');
Route::post('admin_shop_update','Admin\Admin_loginController@admin_shop_update')->middleware('checkLogin');

Route::post('admin_black','Admin\Admin_loginController@admin_black')->middleware('checkLogin');
Route::post('admin_delete','Admin\Admin_loginController@admin_delete')->middleware('checkLogin');

//商品展示
Route::any('goodsList','Amindbackstage\Headquarters@goodsList')->middleware('checkLogin');
//商品添加
Route::post('upload','Amindbackstage\Headquarters@upload')->middleware('checkLogin');
Route::post('goodsAdd','Amindbackstage\Headquarters@goodsAdd')->middleware('checkLogin');
Route::post('goods_type','Amindbackstage\Headquarters@goods_type')->middleware('checkLogin');   //分类
//商品删除
Route::post('goodsdelete','Amindbackstage\Headquarters@goodsdelete')->middleware('checkLogin');
Route::post('admin_goodsUpdate','Amindbackstage\Headquarters@admin_goodsUpdate')->middleware('checkLogin');
Route::post('admin_goodsInfo','Amindbackstage\Headquarters@admin_goodsInfo')->middleware('checkLogin');

Route::post('admin_Assemble','Amindbackstage\Headquarters@admin_Assemble')->middleware('checkLogin');

Route::post('admin_Assemble_list','Amindbackstage\Headquarters@admin_Assemble_list')->middleware('checkLogin');   //

Route::post('admin_Limited_list','Amindbackstage\Headquarters@admin_Limited_list')->middleware('checkLogin');

Route::post('admin_Limited','Amindbackstage\Headquarters@admin_Limited')->middleware('checkLogin');

Route::post('admin_typeInfo','Amindbackstage\Headquarters@admin_typeInfo')->middleware('checkLogin');

Route::post('admin_typeAdd','Amindbackstage\Headquarters@admin_typeAdd')->middleware('checkLogin');

Route::post('admin_typeUpdate','Amindbackstage\Headquarters@admin_typeUpdate')->middleware('checkLogin');

Route::get('admin_typeDelete','Amindbackstage\Headquarters@admin_typeDelete')->middleware('checkLogin');





