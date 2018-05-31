<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/30
 * Time: 18:35
 */
namespace app\receipt\controller;

use think\Lang;
/**
 * 发票模板
 * Class ReceiptModel
 * @package app\receipt\controller
 */
class ReceiptModel extends Base{

    public function index(){
        $field = 'rm_id,model,model_file,create_time';
        $where = '';
        $data = $this->receiptModels()->selectPage($field, $where);
        $this->page($data);
        $this->assign("retmodels", $data);
        return $this->fetch('retmodel/index');
    }

    public function one(){
        $id = input('param.rm_id');
        $where = array('rm_id'=>$id);
        $field = 'rm_id,model,model_file,create_time';
        $data = $this->receiptModels()->select($field, $where);
        echo json_encode($data);
    }
    public function add(){
        return $this->fetch('retmodel/add');
    }

    public function save(){
        $data = input('post.');
        $data['model_file'] = $this->getExcelFile();
        //var_dump($data);exit();
        $data['create_time'] = date('Y-m-d H:i:s', time());
        //基本验证
        $validate = $this->validate($data, 'ReceiptModels');
        if(true !== $validate){
            return $this->error("$validate");
        }
        $result = $this->receiptModels()->add($data, '');
        if(!$result){
            return $this->error(Lang::get('add fail'));
        }
        return $this->success(Lang::get('add success'), 'ReceiptModel/index');

    }

    public function edit(){
        $id = input('param.rm_id');
        $where = array('rm_id'=>$id);
        $find = $this->receiptModels()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind receipt model'));
        }
        $this->assign('retmodel', $find);
        return $this->fetch('retmodel/update');
    }

    public function esave(){
        $id = input('param.rm_id');
        $where = array('rm_id'=>$id);
        $data = input('post.');
        $data['model_file'] = $this->getExcelFile();
        $edit = $this->receiptModels()->update($data, $where);
        if(!$edit){
            return $this->error(Lang::get('edit fail'));
        }
        return $this->success(Lang::get('edit success'), 'ReceiptModel/index');
    }

    public function del(){
        $id = input('param.rm_id');
        //var_dump($id);exit();
        $where = array('rm_id'=>$id);
        $find = $this->receiptModels()->findById($where);
        if(!$find){
            return $this->error(Lang::get('unfind receipt model'));
        }
        $del = $this->receiptModels()->del($where);
        if(!$del){
            return $this->error(Lang::get('del fail'));
        }
        return $this->success(Lang::get('del success'), 'ReceiptModel/index');
    }

    /**
     * 模糊查询
     * @return mixed
     */
    public function search(){
        $search = input('param.search');
        $field = 'rm_id,model,model_file,create_time';
        $where = array('model'=>['like', "%$search%"]);
        $data = $this->receiptModels()->selectPage($field, $where);
        $this->page($data);
        $this->assign("retmodels", $data);
        return $this->fetch('retmodel/index');
    }
    /**
     * @param $path
     * @return string|void
     */
    public function getExcelFile(){
        $file = request()->file('img');
        //上传文件
        //var_dump($file);exit();
        if(!$file){
            return $this->error(Lang::get('upload file please'));
        }
        $info = $file->move(ROOT_PATH.'public'.DS.'model');
        $type = $info->getExtension();           //文件类型
        //var_dump($type);exit;
        if(($type != 'xls') && ($type != 'xlsx')){
            return $this->error(Lang::get('upload excel file'));
        }
        $path = $info->getPath();
        $fileName = $info->getFilename();
        return $path.DS.$fileName;
    }



}