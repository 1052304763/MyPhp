<?php
namespace Home\Controller;
use Think\Controller;
class RedbagController extends Controller {
    // public function index(){
    //     var_dump(__SELF__);
    //     var_dump($_SERVER["SERVER_NAME"]);
    //     var_dump($_SERVER["HTTP_HOST"]);
    //     var_dump($_SERVER["DOCUMENT_ROOT"]);
    //     var_dump($_SERVER["SCRIPT_NAME"]);
    // }

    public function index(){   
        if (isset($_GET['echostr'])) {
            $this->valid();
        }else{
            $this->responseMsg();
        }
    }
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }else{
            echo $echoStr.'+++'.'ceshi';
            exit;
        }
    }
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
    
        $token = 'ceshi';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
    
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA); 
        $postArr = json_decode($postStr,true);
        $msgType = $postArr['MsgType'];  
        if ($msgType != 'text'){
                $postArr = json_decode($postStr,true);//进入客服动作
                $fromUsername = $postArr['FromUserName'];   //发送者openid
                $data = array(
                    "touser"=>$fromUsername,
                    "msgtype"=>"text",
                    "text" => [
                          "content"=> "点击进入"
                        ],
                    );
                $json = json_encode($data,JSON_UNESCAPED_UNICODE); 
                $access_token = $this->get_accessToken();
                /* 
                 * POST发送https请求客服接口api
                 */
                $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
                //以'json'格式发送post的https请求
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                if (!empty($json)){
                    curl_setopt($curl, CURLOPT_POSTFIELDS,$json);
                }
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                //curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
                $output = curl_exec($curl);
                if (curl_errno($curl)) {
                    echo 'Errno'.curl_error($curl);//捕抓异常
                }
                curl_close($curl);                
        }else if($msgType == 'text'){
                $access_token = $this->get_accessToken();
                $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
                $res = $this->uploadfile($access_token);
                $media_id = $res['media_id'];
                $postArr = json_decode($postStr,true);//进入客服动作
                $fromUsername = $postArr['FromUserName'];   //发送者openid

                $data = array(
                    "touser"=>$fromUsername,
                    "msgtype"=>"miniprogrampage",
                    "miniprogrampage" => [
                          "title"=>"标题",
                          "pagepath"=> "pages/index/index",
                          "thumb_media_id"=> 'Ghpsyu1aLzBfm5x61p21dJxJ_hu9-u7ZGkFxeI_tYd0AgtdD05klI5bSLKjJ00kE'
                        ],
                );
                $json = json_encode($data,JSON_UNESCAPED_UNICODE); 
                //以'json'格式发送post的https请求
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                if (!empty($json)){
                    curl_setopt($curl, CURLOPT_POSTFIELDS,$json);
                }
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                //curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
                $output = curl_exec($curl);
                if (curl_errno($curl)) {
                    echo 'Errno'.curl_error($curl);//捕抓异常
                }
                curl_close($curl);
                if($output == 0){
                    echo 'success';exit;
                }
        }
    }
    /* 调用微信api，获取access_token，有效期7200s -xzz0704 */
    public function get_accessToken(){
        /* 在有效期，直接返回access_token */
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx0f749df67ba98f03&secret=4b9eb437085490760e0f2cc1e3c63399";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);
            $jsoninfo = json_decode($output, true);
            //print_r($jsoninfo);die;
            $access_token = $jsoninfo["access_token"];
            // var_dump($access_token);die;
            if($access_token){
                return $access_token;
            }else{
                return 'api return error';
            }

    }

    public function uploadfile($access_token=""){
            // $access_token = $this->get_accessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$access_token&type=images";
            $file = './Public/images/index1.jpg';
            $ch = curl_init();
            $data = array('name'=>'xxx', 'file'=> new \CURLFile(realpath($file)));
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $output = curl_exec($ch);
            curl_close($ch);
            $media_id = json_decode($output, true);
            return $media_id;
    }
}