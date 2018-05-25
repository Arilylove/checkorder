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
        //执行添加日志操作
        $data = $this->logs()->add($logs, '');
        //var_dump($data);exit();

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


}