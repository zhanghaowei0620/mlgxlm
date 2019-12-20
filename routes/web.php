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
    return view('index');
});

//Route::get('/访问方式', '文件夹名\控制器名@方法名');
Route::get('/index', 'Index\IndexController@index');   //首页

Route::get('/type_lists', 'Index\IndexController@type_lists');   //更多

Route::get('/search_goods', 'Index\IndexController@search_goods');   //搜索商品

Route::get('/mt_sort', 'Index\IndexController@mt_sort');   //首页四大分类

Route::get('/search_shop', 'Index\IndexController@search_shop');   //搜索店铺

Route::get('/index_coupon', 'Index\IndexController@index_coupon');    //首页-优惠券

Route::get('/coupon_receive', 'Index\IndexController@coupon_receive'); //点击领取优惠卷

Route::get('/assemble', 'Index\IndexController@assemble');   //拼团

Route::get('/limited_time', 'Index\IndexController@limited_time');    //首页-限时抢

Route::get('/shop_goods', 'Goods\GoodsController@shop_goods');   //根据店铺id 获取店铺下所有商品

Route::get('/weChat', 'User\UserController@weChat');   //微信登陆

Route::get('/usertime', 'User\UserController@usertime');   //微信登陆

Route::get('/shop_settled', 'User\UserController@shop_settled');    //商家入驻

Route::get('/is_shop_settled', 'User\UserController@is_shop_settled');    //判断已有商家入驻

Route::get('/template', 'User\UserController@template');    //模板消息

Route::get('/shop_type', 'User\UserController@shop_type');     //点击获取主营项目

Route::get('/accessToken', 'User\UserController@accessToken');   //accessToken

Route::post('/bankcard', 'User\UserController@bankcard');   //银行卡接口

Route::post('/xcx_upload', 'User\UserController@upload');   //图片上传（多图）

Route::get('/add_bankcard', 'User\UserController@add_bankcard');   //银行卡接口

Route::post('/vidoes', 'User\UserController@vidoes');   //"发现"视频上传到OSS

Route::post('/uservidoes', 'User\UserController@uservidoes');   //用户评价上传视频

Route::get('/bankcard_list', 'User\UserController@bankcard_list');   //银行卡列表

Route::get('/add_bankcard_delete', 'User\UserController@add_bankcard_delete');   //银行卡解绑（删除）

Route::get('/caselist', 'Goods\GoodsController@caselist');     //案例列表

Route::get('/user_coupon', 'User\UserController@user_coupon');    //个人中心优惠券

Route::get('/couponlist', 'Goods\GoodsController@couponlist');     //优惠卷列表

Route::get('/goodsInfo', 'Goods\GoodsController@goodsInfo');    //获取商品详情信息

Route::get('/type_shop', 'Goods\GoodsController@type_shop');     //根据导航栏子级分类获取店铺

Route::get('/father_type_shop', 'Goods\GoodsController@father_type_shop');      //根据导航栏父级分类获取子级分类及店铺信息

Route::get('/add_cart', 'Goods\GoodsController@add_cart');      //点击商品加入购物车

Route::get('/cartList', 'Goods\GoodsController@cartList');      //购物车列表

Route::get('/moneybuy', 'Goods\GoodsController@moneybuy');      //用分享币来购买服务

Route::get('/cart_delete', 'Goods\GoodsController@cart_delete');    //购物车删除

Route::get('/collectionaddd', 'Goods\GoodsController@collectionaddd');     //查询店铺是否收藏

Route::get('/collectionshop', 'Goods\GoodsController@collectionshop');     //查询商品是否收藏

Route::get('/collectiondele', 'Goods\GoodsController@collectiondele');     //收藏商品删除

Route::get('/shop_collection_dele', 'Goods\GoodsController@shop_collection_dele');     //收藏店铺删除

Route::get('/add_collection', 'Goods\GoodsController@add_collection');     //商品加入收藏

Route::get('/collection_list', 'Goods\GoodsController@collection_list');      //商品收藏列表

Route::get('/shop_collection', 'Goods\GoodsController@shop_collection');      //店铺收藏

Route::get('/shop_collection_list', 'Goods\GoodsController@shop_collection_list');       //店铺收藏列表

Route::get('/coupon_list_all', 'Order\OrderController@coupon_list_all');      //查询商品下所有的优惠卷

Route::get('/refund_add', 'Order\OrderController@refund_add');      //分享币退款

Route::get('update_static_del', 'Order\OrderController@update_static_del');  //删除订单

Route::post('/up_status_add', 'Order\OrderController@up_status_add');      //修改用户订单状态

Route::get('/order_insert', 'Order\OrderController@order_insert');      //普通订单  拼团订单  优惠卷订单  限时抢订单

Route::get('/open_order_list', 'Order\OrderController@open_order_list');      //拼团列表

Route::get('/order_list', 'Order\OrderController@order_list');     //订单列表

Route::get('/re_refund_add', 'Order\OrderController@re_refund_add');

Route::get('/order_status_list', 'Order\OrderController@order_status_list');     //根据订单状态获取订单信息

Route::get('/order_detail', 'Order\OrderController@order_detail');     //订单详情

Route::get('/goods_evaluate', 'Order\OrderController@goods_evaluate'); //订单商品评价

Route::post('/evaluate_list', 'Order\OrderController@evaluate_list'); //点赞

Route::post('/my_evaluate_list', 'Order\OrderController@my_evaluate_list'); //个人中心我的评价

Route::get('/goods_evaluate_list', 'Order\OrderController@goods_evaluate_list'); //订单商品评价列表

Route::get('/goods_list_evaluate', 'Order\OrderController@goods_list_evaluate'); //订单商品评价入口信息

Route::get('/user_Address', 'User\UserController@user_Address');      //添加用户地址

Route::get('/user_Address_list', 'User\UserController@user_Address_list');    //地址列表

Route::get('update_address', 'User\UserController@update_address');     //地址修改

Route::get('delete_address', 'User\UserController@delete_address');    //地址删除

Route::get('releaseadd', 'User\UserController@releaseadd');    //发布

Route::get('user_center', 'User\UserController@user_center');     //用户中心

Route::get('user_update', 'User\UserController@user_update');      //修改用户信息

Route::get('user_history', 'User\UserController@user_history');     //我的足迹

Route::get('saveToOss', 'User\UserController@saveToOss');     //视频上传到Oss

Route::get('saveToOss1', 'User\UserController@saveToOss1');     //图片上传到Oss

Route::post('releaselist', 'User\UserController@releaselist');     //发现列表

Route::post('releaselist_Detail', 'User\UserController@releaselist_Detail');     //发现列表-详情

Route::post('release_Fabulous', 'User\UserController@release_Fabulous');   //发现-点赞

Route::post('release_comment', 'User\UserController@release_comment');   //发现-评论

Route::post('release_Fabulous_count', 'User\UserController@release_Fabulous_count');    //点赞个数

Route::post('admin_comment', 'User\UserController@admin_comment');    //更多评论

Route::get('admin_juli', 'User\UserController@admin_juli');

Route::get('user_sign', 'User\UserController@user_sign');    //签到

Route::get('user_sign_add', 'User\UserController@user_sign_add');    //查询每日是否签到

Route::get('/whole_shop', 'Goods\GoodsController@whole_shop');    //全部店铺

Route::get('/nearby_shop', 'Goods\GoodsController@nearby_shop');   //附近店铺

//Route::get('/displace', 'Goods\GoodsController@displace');   //置换商城


Route::post('index_reseller_Apply', 'Reseller\ResellerController@index_reseller_Apply');   //分销-申请

Route::post('index_rellerList', 'Reseller\ResellerController@index_rellerList');    //分销中心-列表

Route::post('index_reseller_goodsList', 'Reseller\ResellerController@index_reseller_goodsList');   //分销中心-商品列表

Route::post('index_reseller_goodsDetail', 'Reseller\ResellerController@index_reseller_goodsDetail');     //分销中心-商品详情

Route::post('index_reseller_orderAdd', 'Reseller\ResellerController@index_reseller_orderAdd');   //分校订单-生成

Route::post('index_reseller_Choose_payment', 'Reseller\ResellerController@index_reseller_Choose_payment');   //分校订单-选择支付方式

Route::post('reseller_order_Confirm_receipt', 'Reseller\ResellerController@reseller_order_Confirm_receipt');   //确认收货

Route::post('index_reseller_Topay', 'Reseller\ResellerController@index_reseller_Topay');   //分校订单-去支付

Route::post('reseller_user_AddressDetail', 'Reseller\ResellerController@reseller_user_AddressDetail');    //根据地址id 获取详情信息

Route::post('index_reseller_orderList', 'Reseller\ResellerController@index_reseller_orderList');

Route::post('reseller_order_information', 'Reseller\ResellerController@reseller_order_information');

Route::post('note_Notify', 'Reseller\ResellerController@note_Notify');

Route::post('my_team', 'Reseller\ResellerController@my_team');   //我的团队

Route::post('my_team_Add', 'Reseller\ResellerController@my_team_Add');   //根据邀请码添加分销员

Route::post('user_reseller_List', 'Reseller\ResellerController@user_reseller_List');   //个人中心-分销中心

Route::post('reseller_my_Profit', 'Reseller\ResellerController@reseller_my_Profit');   //分销排行

Route::post('reseller_user_address', 'Reseller\ResellerController@reseller_user_address');    //获取用户默认地址

Route::get('admin_accessToken2', 'Reseller\ResellerController@admin_accessToken2');

Route::post('admin_my_team', 'Amindbackstage\Headquarters@admin_my_team')->middleware('checkLogin');   //我的团队

Route::post('admin_Application_reseller_list', 'Amindbackstage\Headquarters@admin_Application_reseller_list')->middleware('checkLogin');

Route::get('admin_accessToken1', 'Amindbackstage\Headquarters@admin_accessToken1')->middleware('checkLogin');

Route::get('admin_Limited_list_update_status', 'Amindbackstage\Headquarters@admin_Limited_list_update_status');  //定时任务 -定时查找过期限时抢数据 修改状态

Route::post('invite_userInfo', 'Invite\InviteController@invite_userInfo');  //生成团队

Route::post('invite_friend', 'Invite\InviteController@invite_friend');   //邀请好友

Route::post('open_red_packet', 'Invite\InviteController@open_red_packet');   //拆红包

Route::post('draw_package', 'Invite\InviteController@draw_package');   //新人好礼


Route::get('test', 'Invite\InviteController@test');










/*
 * 后台
 */
//登录接口
Route::any('user','Admin\Admin_loginController@user');

Route::post('admin_forgetPwd','Admin\Admin_loginController@admin_forgetPwd');   //忘记密码-发送短信

Route::post('admin_message_check','Admin\Admin_loginController@admin_message_check');    //验证短信是否正确

Route::post('admin_passwordUpdate','Admin\Admin_loginController@admin_passwordUpdate');  //忘记密码-修改密码

Route::post('admin_set_rebate','Admin\Admin_loginController@admin_set_rebate');  //分销商设置返利比例

Route::post('admin_rebate_detail','Admin\Admin_loginController@admin_rebate_detail');  //查看分销商返利比例

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
Route::any('couponexhibition','Admin\Admin_loginController@couponexhibition');
//->middleware('checkLogin')
//优惠卷删除
Route::any('coupontele','Admin\Admin_loginController@coupontele');
//优惠卷搜索
Route::any('couponsearch','Admin\Admin_loginController@couponsearch')->middleware('checkLogin');
//Route::any('couponsearch','Admin\Admin_loginController@couponsearch');
//优惠卷添加
Route::any('couponinsert','Admin\Admin_loginController@couponinsert')->middleware('checkLogin');
//店铺管理
Route::post('admin_shop','Admin\Admin_loginController@admin_shop')->middleware('checkLogin'); //店铺详情信息
Route::post('admin_shop_update','Admin\Admin_loginController@admin_shop_update')->middleware('checkLogin');   //店铺信息修改

Route::post('admin_black','Admin\Admin_loginController@admin_black')->middleware('checkLogin');    //用户拉黑
Route::post('admin_delete','Admin\Admin_loginController@admin_delete')->middleware('checkLogin');   //用户删除

Route::get('admin_accessToken','Admin\Admin_loginController@admin_accessToken');

Route::get('admin_weixin_code','Admin\Admin_loginController@admin_weixin_code');

//商品展示
Route::any('goodsList','Amindbackstage\Headquarters@goodsList')->middleware('checkLogin');
//商品添加
Route::post('upload','Amindbackstage\Headquarters@upload')->middleware('checkLogin');
Route::post('goodsAdd','Amindbackstage\Headquarters@goodsAdd')->middleware('checkLogin');
Route::post('goods_type','Amindbackstage\Headquarters@goods_type')->middleware('checkLogin');   //分类
//商品删除
Route::post('goodsdelete','Amindbackstage\Headquarters@goodsdelete')->middleware('checkLogin');
Route::post('admin_goodsUpdate','Amindbackstage\Headquarters@admin_goodsUpdate')->middleware('checkLogin');       //获取商品信息
Route::post('admin_goodsInfo','Amindbackstage\Headquarters@admin_goodsInfo')->middleware('checkLogin');    //商品信息修改

Route::post('admin_Assemble','Amindbackstage\Headquarters@admin_Assemble')->middleware('checkLogin');    //查看商品是否开启拼团

Route::post('admin_Assemble_list','Amindbackstage\Headquarters@admin_Assemble_list')->middleware('checkLogin');   //拼团列表

Route::post('admin_Limited_list','Amindbackstage\Headquarters@admin_Limited_list')->middleware('checkLogin');   //限时抢列表

Route::post('admin_Limited','Amindbackstage\Headquarters@admin_Limited')->middleware('checkLogin');    //查看商品是否开启限时抢

Route::post('admin_typeInfo','Amindbackstage\Headquarters@admin_typeInfo')->middleware('checkLogin');   //分类管理

Route::post('admin_typeAdd','Amindbackstage\Headquarters@admin_typeAdd')->middleware('checkLogin');    //分类添加

Route::post('admin_typeUpdate','Amindbackstage\Headquarters@admin_typeUpdate')->middleware('checkLogin');    //分类修改

Route::post('admin_typeDelete','Amindbackstage\Headquarters@admin_typeDelete')->middleware('checkLogin');    //分类删除

Route::post('admin_orderList','Amindbackstage\Headquarters@admin_orderList')->middleware('checkLogin');   //订单列表

Route::post('admin_orderDetail','Amindbackstage\Headquarters@admin_orderDetail')->middleware('checkLogin');    //订单详情

Route::post('admin_caseAdd','Amindbackstage\Headquarters@admin_caseAdd')->middleware('checkLogin');   //案例添加

Route::post('admin_caseList','Amindbackstage\Headquarters@admin_caseList')->middleware('checkLogin');       //案例列表

Route::post('admin_caseDelete','Amindbackstage\Headquarters@admin_caseDelete')->middleware('checkLogin');     //案例删除

Route::post('admin_caseUpdate','Amindbackstage\Headquarters@admin_caseUpdate')->middleware('checkLogin');    //案例修改

Route::post('admin_shop_goodsInfo','Amindbackstage\Headquarters@admin_shop_goodsInfo')->middleware('checkLogin');   //获取店铺下所有商品

Route::post('admin_apply_reseller','Amindbackstage\Headquarters@admin_apply_reseller')->middleware('checkLogin');     //申请成为分销商

Route::post('admin_apply_reseller_list','Amindbackstage\Headquarters@admin_apply_reseller_list')->middleware('checkLogin');     //分销商申请列表

Route::post('admin_reseller_examine','Amindbackstage\Headquarters@admin_reseller_examine')->middleware('checkLogin');   //分销商审核

Route::post('admin_reseller_list','Amindbackstage\Headquarters@admin_reseller_list')->middleware('checkLogin');        //分销商列表

Route::post('admin_reseller_upload_goods','Amindbackstage\Headquarters@admin_reseller_upload_goods')->middleware('checkLogin');         //上传分销商品

Route::post('admin_reseller_goods_list','Amindbackstage\Headquarters@admin_reseller_goods_list')->middleware('checkLogin');          //分销商品列表

Route::post('admin_is_reseller','Amindbackstage\Headquarters@admin_is_reseller')->middleware('checkLogin');     //判断是否为分销商

Route::post('admin_reseller_delete','Amindbackstage\Headquarters@admin_reseller_delete')->middleware('checkLogin');    //取消分销商资格

Route::post('admin_reseller_goods','Amindbackstage\Headquarters@admin_reseller_goods')->middleware('checkLogin');    //是否开启分销-商品

Route::post('admin_reseller_order','Amindbackstage\Headquarters@admin_reseller_order')->middleware('checkLogin');    //是否开启分销-商品

Route::post('admin_reseller_goods_update','Amindbackstage\Headquarters@admin_reseller_goods_update')->middleware('checkLogin');   //分销商品修改

Route::post('admin_reseller_goods_delete','Amindbackstage\Headquarters@admin_reseller_goods_delete')->middleware('checkLogin');   //分销商品删除

Route::post('admin_reseller_Confirm_shipment','Amindbackstage\Headquarters@admin_reseller_Confirm_shipment')->middleware('checkLogin');   //确认发货

Route::post('admin_Logistics_type','Amindbackstage\Headquarters@admin_Logistics_type')->middleware('checkLogin');       //查询所有物流方式

Route::post('admin_reseller_order_information','Amindbackstage\Headquarters@admin_reseller_order_information')->middleware('checkLogin');    //物流信息-使用快递鸟

Route::post('admin_rule_Add','Amindbackstage\Headquarters@admin_rule_Add')->middleware('checkLogin');   //协议添加

Route::post('admin_rule_List','Amindbackstage\Headquarters@admin_rule_List')->middleware('checkLogin');   //协议列表

Route::post('admin_rule_Update','Amindbackstage\Headquarters@admin_rule_Update')->middleware('checkLogin');   //协议修改

Route::post('admin_rule_Delete','Amindbackstage\Headquarters@admin_rule_Delete')->middleware('checkLogin');   //协议删除

Route::post('admin_rule_Detail','Amindbackstage\Headquarters@admin_rule_Detail')->middleware('checkLogin');   //协议详情



Route::post('test','Test\TestController@test')->middleware('checkLogin');          //微信支付

Route::post('getInfo','Test\TestController@getInfo')->middleware('checkLogin');     //微信获取用户openid

Route::post('weixinPay/notify','Test\TestController@notify')->middleware('checkLogin');     //微信支付-异步回调






