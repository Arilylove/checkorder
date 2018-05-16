<?php
/**
 * Created by PhpStorm.
 * User: HXL
 * Date: 2017/5/11
 * Time: 11:37
 */
namespace app\admin\common;

use think\Db;

class Common{
    public function _initialize(){
        //判断用户是否已经登录
        if (!isset($_SESSION['adId'])) {
            $this->error('对不起,您还没有登录!请先登录', 'Login/login');
        }
    }


}
