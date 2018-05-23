<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/21
 * Time: 15:13
 */
namespace app\auth\controller;

use app\auth\model\RoleWays;

class RoleWay extends Base{
    public function roleWays(){
        $roleWays = new RoleWays();
        return $roleWays;
    }

    public function index(){
        $field = "id,role_id,wid,create_time";
        $where = '';
        $order = 'create_time desc';
        $data = $this->roleWays()->page($field, $where, $order);
        $this->assignPage($data);
        $this->assign('roleways', $data);
        return $this->fetch('roleway/index');
    }




}