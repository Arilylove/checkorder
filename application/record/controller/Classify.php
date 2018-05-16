<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/14
 * Time: 15:21
 */
namespace app\record\controller;

class Classify extends Base{

    /**
     * 跳转到添加国家页面
     * @return mixed
     */
    public function aCla(){
        return $this->fetch('cla/add');
    }
    /*
     * 添加国家
     * */
    public function addCla(){
        $classify =input('post.');
        $validate = $this->validate($classify, 'classifies');
        if(true !== $validate){
            return $this->error(" $validate ");
        }
        $result = $this->classifies()->add($classify);
        if (!$result){
            return $this->error('添加失败');
        }
        return $this->success('添加成功', 'Classify/index');
    }
    /**
     * 跳转到批量添加页
     */
    public function batchACla(){
        return $this->fetch('cla/batchadd');
    }

    /**
     * 批量添加action
     */
    public function batchAddCla(){
        $post = input('post.');
        $state = $post['classify'];
        //var_dump($post);exit();
        //1.先验证都通过了
        for($i=0;$i<count($state);$i++){
            $classify['classify'] = $state[$i];
            $validate = $this->validate($classify, 'Classifies');
            if(true !== $validate){
                return $this->error(" $validate ");
            }
            $insertAll[$i]['classify'] = $state[$i];

        }
        $result = $this->classifies()->insertAll($insertAll);
        //var_dump($result);exit();
        if($result < 1){
            return $this->error("添加失败");
        }
        return $this->success("添加成功", 'Classify/index');

    }

    public function listCla(){
        return $this->fetch('cla/index');
    }

    /**
     * @return mixed
     * 问题分类列表
     */
    public function index(){
        $where = '';
        $field = 'pcId,classify';
        $num = 10;
        $count = $this->classifies()->count($where);
        $data = $this->classifies()->selectPage($field, $where, $num, $count);
        //分页
        $this->page($data);
        $this->assign('classify', $data);
        return $this->fetch('cla/index');
    }

    /*
    *查询修改问题分类的信息，json数据
    */
    public function classify(){
        $pcId = input('post.pcId');
        $field = 'pcId,classify';
        $where = array('pcId'=>$pcId);
        $data = $this->classifies()->select($field, $where);
        echo json_encode($data);
    }
    /**
     * 跳转到更新页面
     * @return mixed
     */
    public function eCla(){
        $pcId = input('param.pcId');
        //var_dump($sid);exit();
        $where = array('pcId'=>$pcId);
        $find = $this->classifies()->findById($where);
        if (!$find){
            return $this->error('未找到该国家信息');
        }
        $where = array('pcId'=>$pcId);
        $classify = $this->classifies()->findById($where);
        //var_dump($state);exit();
        $this->assign('classify', $classify);
        return $this->fetch('cla/update');
    }

    /*
     * 修改国家信息
     * */
    public function editCla(){
        $pcId = input('param.pcId');
        //var_dump($sid);exit();
        $where = array('pcId'=>$pcId);
        $find = $this->classifies()->findById($where);
        if (!$find){
            return $this->error('未找到该问题分类信息');
        }
        $classify = input('param.classify');
        $p_classify = array(
            'pcId'=>$pcId,
            'classify'=>$classify
        );
        $result = $this->classifies()->update($p_classify, $where);
        //var_dump($result);exit();
        if (!$result){
            return $this->error('修改失败');
        }
        return $this->success('修改成功', 'Classify/index');
    }
    /*
     * 删除国家
     * */
    public function delCla(){
        $sid = input('param.pcId');
        //var_dump($sid);exit();
        $result = $this->delSta($sid);
        return $result;
    }
    public function delSta($sid){
        $where = array('pcId'=>$sid);
        $find = $this->classifies()->findById($where);
        if (!$find){
            return $this->error('未找到该问题分类信息');
        }
        $delete = $this->classifies()->del($where);
        if (!$delete){
            return $this->error('删除失败');
        }
        return $this->success('删除成功', 'Classify/index');
    }

    /**
     * @return mixed
     * 模糊查询
     */
    public function search(){
        $search = input('param.search');
        $num = 10;
        $data = $this->classifies()->searchLike($search, $num);
        $this->page($data);
        $this->assign('classify', $data);
        return $this->fetch('cla/index');

    }

}