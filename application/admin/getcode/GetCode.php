<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/22
 * Time: 10:51
 */
namespace app\admin\getcode;

class GetCode{
    /*
     * 用http方式获取注册码
     * get方式
     * */
    public function getCode($operatetype, $operatoruid, $operatorpwd, $companycode, $mac, $expirydate, $requestid){
        /*
         * POST方式传值
         * */
        $params = 'operatetype='.$operatetype.'&operatoruid='.$operatoruid.'&operatorpwd='
                  .$operatorpwd.'&companycode='.$companycode.'&mac='.$mac.'&expirydate='
                  .$expirydate.'&requestid='.$requestid;
        $url = 'http://192.168.0.2:18091?';
        //var_dump($url);exit();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt ($curl, CURLOPT_HEADER, 0);   //头文件信息做数据流输出
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Length: 344;charset=utf-8"));
        //https设置
        date_default_timezone_set('PRC');//使用cookies时，必须先进行时区的设置；
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);//对认证证书来源的检查从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true); //启用POST提交
        curl_setopt($curl,CURLOPT_POSTFIELDS, $params);

        $outcurl = curl_exec($curl);
        curl_close($curl);
        return $outcurl;
    }

}

