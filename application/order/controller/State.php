<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/8
 * Time: 14:05
 */
namespace app\order\controller;

use think\Db;
class State extends Base {

    /**
     * 跳转到添加国家页面
     * @return mixed
     */
    public function aSt(){
        $this->authVerify();
        return $this->fetch('state/add');
    }
    /*
     * 添加国家
     * */
    public function addStates(){
        $auth = $this->auth('State', 'aSt');
        if(!$auth){
            return $this->error("对不起,没有权限");
        }
        $states =input('post.');
        $validate = $this->validate($states, 'States');
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->state()->add($states);
        if (!$result){
            return $this->error('添加失败');
        }
        return $this->success('添加成功', 'State/stLi');
    }
    /**
     * 跳转到批量添加页
     */
    public function batchASt(){
        $auth = $this->auth('State', 'aSt');
        if(!$auth){
            return $this->error("对不起,没有权限");
        }
        return $this->fetch('state/batchadd');
    }

    /**
     * 批量添加action
     */
    public function batchAddSt(){
        $auth = $this->auth('State', 'aSt');
        if(!$auth){
            return $this->error("对不起,没有权限");
        }
        $post = input('post.');
        $state = $post['state'];
        //var_dump(count($state));exit();
        //1.先验证都通过了
        foreach ($state as $k=>$value){
            $state['state'] = $value;
            $validate = $this->validate($state, 'States');
            if(true !== $validate){
                return $this->error(" $validate ");
            }
            $insertAll[$k]['state'] = $value;

        }
        /* $sql = Db::table('state')->getLastSql();
         var_dump($sql);exit();
         var_dump($insertAll);exit();*/
        //2.再批量添加
        /*foreach ($insertAll as $states){
            //var_dump($states);exit();
            $result = $this->state()->add($states);
        }*/
        $result = $this->state()->insertAll($insertAll);
        //var_dump($result);exit();
        if($result < 1){
            return $this->error("添加失败");
        }
        return $this->success("添加成功", 'State/stLi');

    }

    public function listS(){
        return $this->fetch('state/states');
    }

    /**
     * @return mixed
     * 国家列表
     */
    public function stLi(){
        $this->authVerify();
        $where = '';
        $field = 'sid,state';
        $num = 10;
        $count = $this->state()->count($where);
        $data = $this->state()->selectPage($field, $where, $num, $count);
        //分页
        $this->page($data);
        $this->assign('states', $data);
        return $this->fetch('state/states');
    }

    /*
    *查询修改国家的信息，json数据
    */
    public function states(){
        $stId = input('sid/s');
        $field = 'sid,state';
        $where = array('sid'=>$stId);
        $data = $this->state()->select($field, $where);
        echo json_encode($data);
    }
    /**
     * 跳转到更新页面
     * @return mixed
     */
    public function eSt(){
        $this->authVerify();
        $sid = input('sid/s');
        //var_dump($sid);exit();
        $where = array('sid'=>$sid);
        $find = $this->state()->findById($where);
        if (!$find){
            return $this->error('未找到该国家信息');
        }
        $field = 'sid,state';
        $where = array('sid'=>$sid);
        $state = $this->state()->select($field, $where);
        //var_dump($state);exit();
        $this->assign('state', $state['0']);
        return $this->fetch('state/update');
    }

    /*
     * 修改国家信息
     * */
    public function editStates(){
        $auth = $this->auth('State', 'eSt');
        if(!$auth){
            return $this->error("对不起,没有权限");
        }
        $sid = input('sid/s');
        //var_dump($sid);exit();
        $where = array('sid'=>$sid);
        $find = $this->state()->findById($where);
        if (!$find){
            return $this->error('未找到该国家信息');
        }
        $state = input('param.state');
        $states = array(
            'sid'=> $sid,
            'state'=> $state
        );
        $result = $this->state()->update($states, $where);
        //var_dump($result);exit();
        if (!$result){
            return $this->error('修改失败');
        }
        return $this->success('修改成功', 'State/stLi');
    }
    /*
     * 删除国家
     * */
    public function delStates(){
        $this->authVerify();
        $sid = input('param.sid');
        //var_dump($sid);exit();
        $result = $this->delSta($sid);
        return $result;
    }
    public function delSta($sid){
        $where = array('sid'=>$sid);
        $find = $this->state()->findById($where);
        if (!$find){
            return $this->error('未找到该国家');
        }
        $delete = $this->state()->del($where);
        if (!$delete){
            return $this->error('删除失败');
        }
        return $this->success('删除成功', 'State/stLi');
    }

    /**
     * @return mixed
     * 模糊查询
     */
    public function search(){
        $search = input('param.search');
        $num = 10;
        $data = $this->state()->searchLike($search, $num);
        $this->page($data);
        $this->assign('states', $data);
        return $this->fetch('state/states');

    }


}