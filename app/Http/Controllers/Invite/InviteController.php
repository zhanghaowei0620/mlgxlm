<?php

namespace App\Http\Controllers\Invite;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InviteController extends Controller
{
    //邀请朋友拆红包
    public function invite_userInfo(Request $request){
        $openid =$request->input('openid');
        $userInfo = DB::table('mt_user')->where('openid',$openid)->first(['uid','wx_headimg','is_team']);
        $uid = $userInfo->uid;
        $is_team = $userInfo->is_team;
        $teamInfo = DB::table('mt_invitation')->where('uid',$uid)->first();
        if($is_team == 0){
            if(!$teamInfo){
                $insert = [
                    'uid'=>$uid,
                    'money'=>120,
                    'current_num'=>1,
                    'f_uid'=>$uid,
                    'create_time'=>time()
                ];
                $insert_team = DB::table('mt_invitation')->insert($insert);
                if($insert_team){
                    DB::table('mt_user')->where('uid',$uid)->update(['is_team'=>1]);
                    $userInfo1 = DB::table('mt_user')->where('openid',$openid)->get(['uid','wx_headimg','openid'])->toArray();
                    $data = [
                        'code'=>0,
                        'arrayInfo'=>$userInfo1,
                        'msg'=>'数据请求成功'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }else{
                    $data = [
                        'code'=>2,
                        'msg'=>'系统出现错误，请重试'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            }else{
                $arrayInfo = DB::table('mt_invitation')
                    ->join('mt_user','mt_invitation.uid','=','mt_user.uid')
                    ->where('mt_invitation.f_uid',$teamInfo->f_uid)->get(['mt_user.wx_headimg','mt_user.uid','mt_user.openid'])->toArray();

                $data = [
                    'code'=>0,
                    'arrayInfo'=>$arrayInfo,
                    'msg'=>'数据请求成功'
                ];
                $response = [
                    'data' => $data
                ];
                return json_encode($response, JSON_UNESCAPED_UNICODE);

            }
        }else{
            $arrayInfo = DB::table('mt_invitation')
                ->join('mt_user','mt_invitation.uid','=','mt_user.uid')
                ->where('mt_invitation.f_uid',$teamInfo->f_uid)->get(['mt_user.wx_headimg','mt_user.uid','mt_user.openid'])->toArray();
            $data = [
                'code'=>0,
                'arrayInfo'=>$arrayInfo,
                'msg'=>'数据请求成功'
            ];
            $response = [
                'data' => $data
            ];
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

    }
    public function invite_friend(Request $request){
        $openid = $request->input('openid2');   //拉取用户的人的openid
        $openid1 = $request->input('openid1');  //被拉取用户的openid
        $userInfo = DB::table('mt_user')->where('openid',$openid)->first();  //拉取用户的人的用户信息
        $uid = $userInfo->uid;
        $userInfo1 = DB::table('mt_user')->where('openid',$openid1)->first(['uid','is_invitation']);  //被拉取用户的人的用户信息
        $uid1 = $userInfo1->uid;
        $is_invitation = $userInfo1->is_invitation;
        if($userInfo1 && $is_invitation==0){
            $inviteInfo = DB::table('mt_invitation')->where('uid',$uid)->first();
            if($inviteInfo){
                if($inviteInfo->current_num < 6){
                    $arrInfo = DB::table('mt_invitation')->where('uid',$uid1)->first();
                    if(!$arrInfo){
                        $insert = [
                            'uid'=>$uid1,
                            'money'=>120,
                            'current_num'=>$inviteInfo->current_num+1,
                            'f_uid'=>$inviteInfo->f_uid,
                            'create_time'=>time()
                        ];
                        $insert_inv =  DB::table('mt_invitation')->insertGetId($insert);
                        if($insert_inv){
                            $uid1Info = DB::table('mt_invitation')->where('uid',$uid1)->first();
                            DB::table('mt_invitation')->where('f_uid',$uid1Info->f_uid)->update(['current_num'=>$uid1Info->current_num]);
                            DB::table('mt_user')->where('uid',$uid1)->update(['is_invitation'=>1]);
                            $data = [
                                'code'=>0,
                                'msg'=>'邀请成功'
                            ];
                            $response = [
                                'data' => $data
                            ];
                            return json_encode($response, JSON_UNESCAPED_UNICODE);
                        }else{
                            $data = [
                                'code'=>2,
                                'msg'=>'系统出现错误，请重试'
                            ];
                            $response = [
                                'data' => $data
                            ];
                            die(json_encode($response, JSON_UNESCAPED_UNICODE));
                        }
                    }else{
                        $data = [
                            'code'=>3,
                            'msg'=>'您当前已组成了一个团队，不可再次组队'
                        ];
                        $response = [
                            'data' => $data
                        ];
                        die(json_encode($response, JSON_UNESCAPED_UNICODE));
                    }
                }else{
                    $data = [
                        'code'=>1,
                        'msg'=>'当前团队已满员'
                    ];
                    $response = [
                        'data' => $data
                    ];
                    die(json_encode($response, JSON_UNESCAPED_UNICODE));
                }
            }else{
                $data = [
                    'code'=>4,
                    'msg'=>'当前用户并未处于团队当中，不能邀请，请先创建团队'
                ];
                $response = [
                    'data' => $data
                ];
                die(json_encode($response, JSON_UNESCAPED_UNICODE));
            }


        }else{
            $data = [
                'code'=>0,
                'msg'=>'您邀请的用户不存在或者已被邀请过'
            ];
            $response = [
                'data' => $data
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }



    }
    public function open_red_packet(Request $request){
        $openid = $request->input('openid');
        $userInfo = DB::table('mt_user')->where('openid',$openid)->first(['uid']);
        $uid = $userInfo->uid;
        $mt_invitationInfo = DB::table('mt_invitation')->where('f_uid',$uid)->get(['uid','money','team_num'])->toArray();
        $mt_invitation = DB::table('mt_invitation')->where('uid',$uid)->first(['uid','money','team_num']);
        $everyBody = $mt_invitation->money/$mt_invitation->team_num;
//        var_dump($everyBody);exit;
        foreach ($mt_invitationInfo as $k=>$v){
            $userArray = DB::table('mt_user')->where('uid',$v->uid)->first();
            $update = [
                'money'=>$userArray->money+$everyBody
            ];
            DB::table('mt_user')->where('uid',$v->uid)->update($update);

            DB::table('mt_user')->where('uid',$uid)->update(['is_team'=>0]);
            DB::table('mt_invitation')->where('f_uid',$uid)->delete();
        }

        $data = [
            'code'=>0,
            'msg'=>'红包拆除成功，请去个人中心中查看分享币余额'
        ];
        $response = [
            'data' => $data
        ];
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }



}