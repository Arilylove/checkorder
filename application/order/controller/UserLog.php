<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/24
 * Time: 17:18
 */
namespace app\order\controller;

class UserLog extends Base{

    /**
     * 用于保存操作日志
     */
    public function setLog($behavior){
        $username = session('orderuser');
        $find = $this->admins()->findById(array('username'=>$username));
        $logs['uid'] = $find['adId'];
        $logs['create_time'] = date('Y-m-d H:i:s', time());
        $logs['behavior'] = $behavior;
        $ip = $_SERVER['REMOTE_ADDR'];
        $logs['user_ip'] = $ip;
        //var_dump($logs);exit();
        //执行添加日志操作
        $data = $this->logs()->add($logs, '');
        //var_dump($data);exit();
        /*//根据ip获取地理位置
        $city = $this->getCity('106.14.7.183');
        var_dump($city);exit();*/

    }
    public function view(){
        $field = 'lid,uid,behavior,create_time';
        $where = '';
        $data = $this->logs()->selectPage($field, $where, 'create_time desc');
        $data = $this->joinUids($data);
        $this->page($data);
        $this->assign('logs', $data);

    }

    /**
     * 根据uid获取用户名
     * @param $data
     * @return mixed
     */
    private function joinUids($data){
        for ($i=0;$i<count($data);$i++){
            $data = $this->joinOne($data);
        }
        return $data;
    }
    private function joinOne($data){
        $len = count($data);
        if($len >= 1){
            for ($i=0;$i<$len;$i++){
                $uid = $data['uid'];
                $findUid = $this->admins()->findById(array('adId'=>$uid));
                $data['username'] = $findUid['username'];
            }
        }else{
            $data['username'] = '';
        }
        return $data;

    }

    /**
     * 获取客户端IP地址 ---感觉用处不是很大，还是有伪装
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    private function get_client_ip($type = 0, $adv = false)
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) return $ip[$type];
        if ($adv) {//高级模式获取(防止伪装)
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) unset($arr[$pos]);
                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    /**
     * 获取 IP  获取地理位置
     * 淘宝IP接口
     * @Return: array
     */
    private function getCity($ip = '')
    {
        if($ip == ''){
            $url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json";
            $ip = json_decode(file_get_contents($url),true);
            $data = $ip;
        }else{
            $url="http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
            $ip = json_decode(file_get_contents($url));
            if((string)$ip->code == '1'){
                return false;
            }
            $data = (array)$ip->data;
        }

        return $data;
    }

}