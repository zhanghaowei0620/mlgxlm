<?php

namespace App\Http\Controllers\Test;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestController extends Controller
{
    public function getInfo(Request $request)
    {
        $code = $request->input("code");
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . env('WX_APP_ID') . "&secret=" . env('WX_KEY') . "&js_code=" . $code . "&grant_type=authorization_code";
        $infos = json_decode(file_get_contents($url));
        $openid = $infos->openid;
        return $openid;
    }

    public function test(Request $request)
    {
//        $re_order_id = $request->input('re_order_id');
        $openid = $request->input('openid');
        //var_dump($openid);exit;
        $appid = env('WX_APP_ID');
        $mch_id = env('wx_mch_id');
        $nonce_str = $this->nonce_str();
        $body = '测试订单-'.mt_rand(1111,9999) . Str::random(6);
        $order_id = 'zhangsan-'.time().mt_rand(11111,99999);//测试订单号 随机生成
        $trade_type = 'JSAPI';
        $notify_url = 'http://lvs.mlgxlm.com/weixinPay/notify';
        //dump($openid);die;
        $spbill_create_ip = $_SERVER['REMOTE_ADDR'];
        $total_fee = 1;//因为充值金额最小是1 而且单位为分 如果是充值1元所以这里需要*100
        //dump($total_fee);die;
        //这里是按照顺序的 因为下面的签名是按照顺序 排序错误 肯定出错
        $post['appid'] = $appid;
        $post['mch_id'] = $mch_id;
        $post['body'] = $body;
        $post['nonce_str'] = $nonce_str;//随机字符串
        $post['notify_url'] = $notify_url;
        $post['openid'] = $openid;
        $post['out_trade_no'] = $order_id;
        $post['spbill_create_ip'] = $spbill_create_ip;//终端的ip
        $post['total_fee'] = $total_fee;//总金额 最低为一块钱 必须是整数
        $post['trade_type'] = $trade_type;
        $post['sign'] = $this->sign($post);//签名

        $post_xml = $this->ArrToXml($post);
        //统一接口prepay_id
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $xml = $this->curlRequest($url, $post_xml);
        $array = $this->xml($xml);//全要大写
//        var_dump($array);exit;
        if ($array['return_code'] == 'SUCCESS' && $array['result_code'] == 'SUCCESS') {

            $time = time();
            //$tmp = '';//临时数组用于签名
            $tmp['appId'] = $appid;
            $tmp['nonceStr'] = $nonce_str;
            $tmp['package'] = 'prepay_id=' . $array['prepay_id'];
            $tmp['signType'] = 'MD5';
            $tmp['timeStamp'] = "$time";

            $data['prepay_id'] = $array['prepay_id'];
            //$data['state'] = 1;
            $data['timeStamp'] = "$time";//时间戳
            $data['nonceStr'] = $nonce_str;//随机字符串
            $data['signType'] = 'MD5';//签名算法，暂支持 MD5
            $data['package'] = 'prepay_id=' . $array['prepay_id'];//统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
            $data['paySign'] = $this->sign($tmp);//签名,具体签名方案参见微信公众号支付帮助文档;
            $data['out_trade_no'] = $order_id;

        } else {
            $data['state'] = 0;
            $data['text'] = "错误";
            $data['return_code'] = $array['return_code'];
            $data['return_msg'] = $array['return_msg'];
        }
        return json_encode($data);
    }

    public function nonce_str()
    {
        $result = '';
        $str = 'QWERTYUIOPASDFGHJKLZXVBNMqwertyuioplkjhgfdsamnbvcxz';
        for ($i = 0; $i < 32; $i++) {
            $result .= $str[rand(0, 48)];
        }
        return $result;
    }

    public function sign($data)
    {
        $wx_key = 'hkxhbjmequurd0bdv1ilnlb0ufq3lurn';
        ksort($data);
        $str = urldecode(http_build_query($data) . '&key=' . $wx_key);
        $sign = strtoupper(md5($str));
        return $sign;
    }
    function curlRequest($url, $data = '')
    {
        $ch = curl_init();
        $params[CURLOPT_URL] = $url;    //请求url地址
        $params[CURLOPT_HEADER] = false; //是否返回响应头信息
        $params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
        $params[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
        $params[CURLOPT_TIMEOUT] = 30; //超时时间
        if (!empty($data)) {
            $params[CURLOPT_POST] = true;
            $params[CURLOPT_POSTFIELDS] = $data;
        }
        $params[CURLOPT_SSL_VERIFYPEER] = false;//请求https时设置,还有其他解决方案
        $params[CURLOPT_SSL_VERIFYHOST] = false;//请求https时,其他方案查看其他博文
        curl_setopt_array($ch, $params); //传入curl参数
        $content = curl_exec($ch); //执行
        curl_close($ch); //关闭连接
        return $content;
    }

    public function http_requests($url, $data = null, $headers = array())
    {
        $curl = curl_init();
        if (count($headers) >= 1) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     * 异步回调处理成功时返回内容
     * @param $msg
     * @return string
     */
    public function notifyReturnSuccess($msg = 'OK')
    {
        return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[' . $msg . ']]></return_msg></xml>';
    }

    /**
     * 异步回调处理失败时返回内容
     * @param $msg
     * @return string
     */
    public function notifyReturnFail($msg = 'FAIL')
    {
        return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[' . $msg . ']]></return_msg></xml>';
    }
    /**
     * 输出xml字符（数组转换成xml）
     * @param $params 参数名称
     * return string 返回组装的xml
     **/
    public function ArrToXml($params)
    {
        if (!is_array($params) || count($params) <= 0) {
            return false;
        }
        $xml = "<xml>";
        foreach ($params as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    function XmlToArr($xml)
    {
        if ($xml == '') return '';
        libxml_disable_entity_loader(true);
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arr;
    }

    public function xml($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }


    /**
     * 微信支付回调
     */
    public function notify(){
        echo 111;exit;
        $xml = file_get_contents("php://input");
        $xml_obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $xml_arr = json_decode(json_encode($xml_obj), true);
        file_put_contents('/wwwroot/mlgxlm/storage/logs/wechat.log', 'XML_ARR:' . print_r($xml_arr, 1) . "\r\n", FILE_APPEND);
        if (($xml_arr['return_code'] == 'SUCCESS') && ($xml_arr['result_code'] == 'SUCCESS')) {
            //修改订单状态


            if ($xml_arr) {
                $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }else{
                $str='<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
            }

            echo $str;
            return $xml_arr;
        }else{
            $str='<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
            echo $str;
            return $xml_arr;
        }
    }


}
