<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/7
 * Time: 17:25
 */
namespace app\receipt\controller;

use think\Controller;
use think\Db;
use app\receipt\crypt\AesCrypt;

class Base extends Controller{
   /* public function _initialize(){
        $username = session('username');
        $status = session('status');
        $a = is_null($username);
        //var_dump($a);exit();
        //判断用户是否已经登录
        if ($a) {
            return $this->error('对不起,您还没有登录!请先登录', 'Login/index');
        }
        $this->assign("username", $username);
        $this->assign("status", $status);
        return true;
    }*/

    /**
     * 分页
     * @param $table
     */
    public function page($table){
        $page = $table->render();
        $currentPage = $table->currentPage();
        $this->assign('currentPage', $currentPage);
        $this->assign('pageOrder', ($currentPage-1)*10);
        $this->assign('page', $page);
    }


    /*
      *修改密码
      * */
    public function update(){
        $username = session('username');
        $this->assign('username', $username);
        return $this->fetch('lic/upPass');
    }

    public function updatePassword(){
        $username = session('username');
        //$this->assign('username', $username);
        $where = array('username'=>$username);
        $admin = Db::table('admin')->where($where)->find();
        if (!$admin){
            return $this->error('该用户不存在');
        }
        //var_dump($admin['password']);exit();
        $this->assign('adId', $admin['adId']);
        $string = new AesCrypt();
        //解密
        $password = $admin['password'];
        //var_dump($password);exit();
        $inputPassword = $string->encrypt(input('param.password'));
        $update = $string->encrypt(input('param.update'));
        $confirm = $string->encrypt(input('param.confirm'));
        if($password != $inputPassword){
            return $this->error('密码输入错误');
        }
        if ($update == $password){
            return $this->error('修改密码同原始密码相同');
        }
        if ($update == ''){
            return $this->error('密码不能为空');
        }
        if ($update != $confirm){
            return $this->error('两次输入密码不相同');
        }
        $result = Db::table('admin')->where('username', $username)->update(['password'=>$update]);
        //var_dump($result);exit();
        if (!$result){
            return $this->error('修改失败');
        }
        session('username', null);
        return $this->success('修改成功,返回登录界面', 'Login/index');

    }

    /**
     * 客户option
     */
    protected function assignClient(){
        $joinTable = 'state';
        $param = 'sid';
        $where = '';
        $field = 'cid,state,client';
        $client = $this->clients()->joinSelect($joinTable, $param, $where, $field);
        //如果一个国家下面有好几个客户
        $this->assign('clients', $client);
    }

    /**
     * 国家option
     */
    protected function assignState(){
        $field = 'sid,state';
        $state = $this->state()->select($field, '');
        $this->assign('state', $state);
    }
    /**
     * 制造商option
     */
    protected function assignManu(){
        $field = 'mfId,manufacturer';
        $manu = $this->manus()->select($field, '');
        $this->assign('manu', $manu);
    }
    /**
     * 基表型号option
     */
    protected function assignMeterType(){
        $field = 'meterId,meterType';
        $meterType = $this->meterTypes()->select($field, '');
        $this->assign('meterType', $meterType);
    }
    /**
     * 电子模块类型option
     */
    protected function assignModelType(){
        $field = 'modelId,modelType';
        $modelType = $this->modelTypes()->select($field, '');
        $this->assign('modelType', $modelType);
    }
    /**
     * 生产负责人option
     */
    protected function assignPrinc(){
        $field = 'pid,productPrinciple,dept,position';
        $principle = $this->principles()->select($field, '');
        $this->assign('principle', $principle);
    }

    /**
     * 表号option
     */
    protected function assignMeter(){
        $field = 'mid,meterNum,oid';
        $meter = $this->meters()->select($field, '');
        $this->assign('meters', $meter);
    }

    protected function assignOrder(){
        $field = "oid,state,client,meterType,modelType,modelStart,modelEnd,modelNum,meterStart,meterEnd,assemStart,assemEnd,deliveryTime,orderNum,manufacturer,productPrinciple,deliveryStatus,orderCycle,assemCycle";
        $order = $this->orders()->select($field, '');
        $this->assign('order', $order);
    }

    /**
     * 日期计算-》周期(单位：日)
     */
    public function countDate($start, $end){
        //刚开始格式为2018-05-10
        $startDate = strtotime($start);
        $endDate = strtotime($end);
        $days = round(($endDate-$startDate)/3600/24) ;
        return $days;
    }

    /**
     * 验证表号
     */
    public function verifyNum($startNum, $endNum){
        //1.长度10,,11,12,13;2.长度相等；3.前四位相同;4.都是数字；5.如果是13位，只保留前面12位。
        $startLen = strlen($startNum);
        $endLen = strlen($endNum);
        $start4 = substr($startNum, 0, 4);
        $end4 = substr($endNum, 0, 4);
        $startIsNum = is_numeric($startNum);
        $endIsNum = is_numeric($endNum);
        if(($startLen != $endLen) || ($start4 != $end4) || (!$startIsNum) || (!$endIsNum)){
            return $this->error("表号必须是数字,前四位相同且长度相等");
        }
        $startFloat = floatval($startNum);
        $endFloat = floatval($endNum);
        $intLength = intval($endFloat-$startFloat);
        //var_dump($intLength);exit();
        if($intLength < 1){
            return $this->error("结束表号要大于开始表号");
        }

    }

    /**
     * Excel导出
     * @param data 导出数据
     * @param title 表格的字段名 字段长度有限制，一般都够用，可以改变 $length1和$length2来增长
     * @return subject 表格主题 命名为主题+导出日期
     */
    function exportExcel($data,$title,$subject){
        Vendor('phpexcel.PHPExcel');
        Vendor('phpexcel.PHPExcel.IOFactory');
        Vendor('phpexcel.PHPExcel.Reader.Excel5');
        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        // Set properties
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        //插入图片
        $objDrawing = new \PHPExcel_Worksheet_Drawing();
        /*设置图片路径 切记：只能是本地图片*/
        $objDrawing->setPath(ROOT_PATH.'public'.DS.'img'.DS.'laison.jpg');
        /*设置图片高度*/
        $objDrawing->setHeight(6.35);//照片高度
        $objDrawing->setWidth(2.28); //照片宽度
        /*设置图片要插入的单元格*/
        $objDrawing->setCoordinates('A1');
        /*设置图片所在单元格的格式*/
        $objDrawing->setOffsetX(5);
        $objDrawing->setRotation(5);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(50);
        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

        $length1=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD');
        $length2=array('A1','B1','C1','D1','E1','F1','G1','H1','I1','J1','K1','L1','M1','N1','O1','P1','Q1','R1','S1','T1','U1','V1','W1','X1','Y1','Z1','AA1','AB1','AC1','AD1');
        //set width
        for($a=0;$a<count($title);$a++){
            $objPHPExcel->getActiveSheet()->getColumnDimension($length1[$a])->setWidth(20);
        }
        //set font size bold
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle($length2[0].':'.$length2[count($title)-1])->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle($length2[0].':'.$length2[count($title)-1])->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle($length2[0].':'.$length2[count($title)-1])->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        // set table header content
        for($a=0;$a<count($title);$a++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($length2[$a], $title[$a]);
        }
        for($i=0;$i<count($data);$i++){
            $buffer=$data[$i];
            $number=0;
            foreach ($buffer as $value) {
                $objPHPExcel->getActiveSheet(0)->setCellValueExplicit($length1[$number].($i+2),$value,\PHPExcel_Cell_DataType::TYPE_STRING);//设置单元格为文本格式
                $number++;
            }
            unset($value);
            $objPHPExcel->getActiveSheet()->getStyle($length1[0].($i+2).':'.$length1[$number-1].($i+2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($length1[0].($i+2).':'.$length1[$number-1].($i+2))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getRowDimension($i+2)->setRowHeight(16);
        }
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        ob_end_clean();//清除缓冲区,避免乱码
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$subject.'('.date('Y-m-d').').xls');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}