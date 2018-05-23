<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/18
 * Time: 17:47
 */
namespace app\auth\controller;

class Auth extends Base{

    public function index(){
        return $this->fetch('auth/auth');
    }

    /**
     * 1.获取该用户角色id
     * 2.获取该角色id下所有方法
     * 3.获取操作名和方法名
     * 4.比较该操作名和方法名是否在该角色下
     * 1表示有权限，0表示无权限
     */
    public function auth($controller, $action){
        $uid = session('uid');
        $ways = $this->getWaysByUid($uid);
        //$controller = request()->controller();
        //$action = request()->action();
        if(in_array($controller, $ways['w_control']) && in_array($action, $ways['w_way'])){
            return 1;
        }
        return 0;
    }

    //查询所有角色
    public function getAllRoles(){
        $field = 'role_id,role_name,remark,create_time,wid,status';
        $data = $this->roles()->select($field, '');
        return $data;
    }

    //查询所有方法
    public function getAllWays(){
        $field = 'wid,w_name,pid,w_control,w_way,url,create_time,status';
        $where = '';
        $data = $this->ways()->select($field, $where);
        return $data;
    }




}