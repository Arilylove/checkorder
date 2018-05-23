<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/7
 * Time: 17:25
 */
namespace app\auth\controller;

use app\auth\model\Admins;
use app\auth\model\Depts;
use app\auth\model\RoleWays;
use app\auth\model\Ways;
use think\Controller;
use think\Db;
use app\auth\model\Roles;

class Base extends Controller{
    /*public function _initialize(){
        $username = session('username');
        $status = session('status');
        $surname = session('surname');
        $a = is_null($username);
        //var_dump($a);exit();
        //判断用户是否已经登录
        if ($a) {
            return $this->error('对不起,您还没有登录!请先登录', 'Login/index');
        }
        $this->assign("username", $username);
        $this->assign("surname", $surname);
        $this->assign("status", $status);
        return true;
    }*/
    /**
     * 根据UID获取该用户所有权限
     * @param $uid
     * @return array|int
     */
    public function getWaysByUid($uid){
        //$uid = session('uid');
        $find = $this->admins()->findById(array('uid'=>$uid));
        $role_id = $find['role_id'];
        $wid = $this->roles()->findById(array('role_id'=>$role_id));
        //查看是否有权限
        $len = strlen($wid['wid']);
        if($len < 1){
            return null;
        }
        //去除最后一个','
        $wid['wid'] = substr($wid['wid'], 0, strlen($wid['wid'])-1);
        $widArr = explode(',', $wid['wid']);
        $ways = array();
        for($i=0;$i<count($widArr);$i++){
            $wid = $widArr[$i];
            $findWay = $this->ways()->findById(array('wid'=>$wid));
            $ways['w_control'][$i] = $findWay['w_control'];
            $ways['w_way'][$i] = $findWay['w_way'];
        }
        return $ways;
    }
    public function assignWays($uid){
        $ways = $this->getWaysByUid($uid);
        //var_dump($ways);exit();
        if(count($ways) == 0){
            $ways['w_control'] = [];
            $ways['w_way'] = [];
            $this->assign('ownways', $ways);
        }else{
            //var_dump($ways);exit();
            $this->assign('ownways', $ways);
        }

    }


    protected function roles(){
        $roles = new Roles();
        return $roles;
    }
    protected function ways(){
        $ways = new Ways();
        return $ways;
    }
    protected function roleways(){
        $roleways = new RoleWays();
        return $roleways;
    }
    protected function depts(){
        $depts = new Depts();
        return $depts;
    }
    protected function admins(){
        $admins = new Admins();
        return $admins;
    }

    /**
     * 分页展示
     * @param $data
     */
    public function assignPage($data){
        $page = $data->render();
        $this->assign('page', $page);
    }
}