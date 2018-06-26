<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/9
 * Time: 11:25
 */
namespace app\order\controller;

use think\Db;
use think\Lang;

class Order extends Base{

    public function index(){
        $this->authVerify();

        //先从订单表中获取全部信息，mid，在由mid获取表号
        $field = "oid,state,client,meterType,modelType,modelStart,modelEnd,modelNum,meterStart,meterEnd,assemStart,assemEnd,deliveryTime,orderNum,manufacturer,productPrinciple,deliveryStatus,orderCycle,assemCycle,customTool,dataVerify,isStatus";
        //只能查看自己业务部的订单
        //如果不是业务部门就没有
        $sale_id = session('sale_id');
        if($sale_id == 0){
            $where = '';
        }else{
            $where = array('sale_id'=>$sale_id);
        }
        $count = $this->orders()->count($where);
        $ord = 'oid asc';
        $orders = $this->orders()->selectPage($where, $count, $ord);
        $order = $this->getJoinId($orders);
        //var_dump($sql);
        $this->page($order);

        $this->assignState();
        $this->assignClient();
        $this->assignManu();

        //var_dump($order);exit();
        $this->assign('orders', $order);
        return $this->fetch("ord/index");

    }

    /**
     * 跳转到添加页
     * @return mixed
     */
    public function aOrd(){
        $auth = $this->auth('Order', 'add');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        //国家，客户，基表型号，电子模块类型，制造商，生产负责人，发货状态都是下拉框选择
        $this->assignState();
        $this->assignClient();
        $this->assignMeterType();
        $this->assignModelType();
        $this->assignManu();
        $this->assignPrinc();
        $this->assignSaleDept();
        return $this->fetch("ord/add");
    }

    /**
     * 添加action
     */
    public function addOrd(){
        $auth = $this->auth('Order', 'add');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $orders = input("post.");
        //基本验证
        $validate = $this->validate($orders, 'Orders');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }

        $startLen = strlen($orders['meterStart']);
        $endLen = strlen($orders['meterEnd']);
       /* var_dump($startLen);
        var_dump($endLen);exit();*/
        //表号验证,允许为空
        $this->verifyMeterNum($orders, $startLen, $endLen);
        //5.如果是13位，只保留前面12位。
        if($startLen == 13){
            $orders['meterStart'] = substr($orders['meterStart'], 0, ($startLen-1));
        }
        if($endLen == 13){
            $orders['meterEnd'] = substr($orders['meterEnd'], 0, ($endLen-1));
        }
        $start = "1".$orders['meterStart'];
        $end = "1".$orders['meterEnd'];
        //计算周期
        if(!intval($orders['deliveryStatus'])){
            $orders['orderCycle'] = $this->countDate($orders['modelStart'], $orders['modelEnd']);
            $orders['assemCycle'] = $this->countDate($orders['assemStart'], $orders['assemEnd']);
            if(($orders['orderCycle'] < 0) || ($orders['assemCycle'] < 0)){
                return $this->error(Lang::get('enddate later than start'));
            }
        }else{
            $orders['orderCycle'] = 0;
            $orders['assemCycle'] = 0;
        }
        //var_dump($orders);exit();
        unset($orders['hidqty']);
        $orders = $this->dealNull($orders);
        //var_dump($orders);exit();
        $addOrder = $this->orders()->add($orders, '');
        if($addOrder < 1){
            return $this->error(Lang::get('add fail'));
        }
        $lastId = $this->orders()->lastSql();

        //添加到表号
        $addMeter = 0;
        //1.两个表号都有
        if(($startLen > 0) && ($endLen > 0)){
            //$meterNums = $this->meterNumList($start, $end);
            $addMeter = $this->meterListAdd($start, $end, $lastId);
        }else if($startLen > 0){
            //2.只有一个表号
            $meters['meterNum'] = $orders['meterStart'];
            $meters['oid'] = $lastId;
            $addMeter = $this->meters()->add($meters, '');
        }
        return $this->success(Lang::get('add success'), 'Order/index');

    }

    /**
     * 跳转到修改页
     * @return mixed
     */
    public function eOrd(){
        $auth = $this->auth('Order', 'edit');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        //国家，客户，基表型号，电子模块类型，制造商，生产负责人，发货状态都是下拉框选择
        $this->assignState();
        $this->assignClient();
        $this->assignMeterType();
        $this->assignModelType();
        $this->assignManu();
        $this->assignPrinc();
        $this->assignSaleDept();
        $oid = input('param.oid');
        $order = $this->orders()->findById(array('oid'=>$oid));
        //国家，客户，基表型号，电子模块类型，制造商，生产负责人
        $order = $this->getOneJoinId($order);
        //根据modelNum查询剩余模块数量
        $oneModel = $this->sumQty($order['modelNum']);
        $order['surplus'] = $oneModel['modelQty'];
        $this->assign('order', $order);
        $page = input('param.page');
        //var_dump($query);exit();
        $this->assign('currentPage', $page);
        return $this->fetch("ord/update");
    }

    /**
     * 修改action
     */
    public function editOrd(){
        $auth = $this->auth('Order', 'edit');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $orders = input("post.");
        //订单号存在
        $oid = $orders['oid'];
        $where = array('oid'=>$oid);
        $find = $this->orders()->findById($where);
        //更新之前获取更新之前的表号
        $meterStart = $find['meterStart'];
        $meterEnd = $find['meterEnd'];
        if(!$find){
            return $this->error(Lang::get('order unexist'));
        }
        $startLen = strlen($orders['meterStart']);
        $endLen = strlen($orders['meterEnd']);

        //基本验证
        $validate = $this->validate($orders, 'Orders');
        //var_dump($validate);exit();
        if(true !== $validate){
            return $this->error(" $validate ");
        }

        //表号验证,允许为空
        $this->verifyMeterNum($orders, $startLen, $endLen);
        //5.如果是13位，只保留前面12位。
        if($startLen == 13){
            $orders['meterStart'] = substr($orders['meterStart'], 0, ($startLen-1));
        }
        if($endLen == 13){
            $orders['meterEnd'] = substr($orders['meterEnd'], 0, ($endLen-1));
        }
        $start = "1".$orders['meterStart'];
        $end = "1".$orders['meterEnd'];
        /*var_dump($start);
        var_dump($end);exit();*/
        //表号是否被其他订单使用
        if($startLen > 0){
            if($endLen > 0){
                //1.两个表号都不为空，需要连续验证是否被使用
                $num = $this->meterNumList($start, $end);
                foreach ($num as $value){
                    $findUsed = $this->meters()->findUserd(array('meterNum'=>$value), $oid);
                }
            }else{
                //2.结束表号为空，只要验证一个表号
                $findUsed = $this->meters()->findUserd(array('meterNum'=>$orders['meterStart']), $oid);
            }
            if($findUsed){
                return $this->error(Lang::get('meternum used'));
            }
        }

        //计算周期
        if(!intval($orders['deliveryStatus'])){
            $orders['orderCycle'] = $this->countDate($orders['modelStart'], $orders['modelEnd']);
            $orders['assemCycle'] = $this->countDate($orders['assemStart'], $orders['assemEnd']);
            if(($orders['orderCycle'] < 0) || ($orders['assemCycle'] < 0)){
                return $this->error(Lang::get('enddate later than start'));
            }
        }else{
            $orders['orderCycle'] = 0;
            $orders['assemCycle'] = 0;
        }

        /*var_dump($meterStart);
        var_dump($meterEnd);exit();*/
        unset($orders['hidqty']);
        //空格处理
        $orders = $this->dealNull($orders);
        //var_dump($orders);exit();
        $edit = $this->orders()->update($orders, $where);
        if($edit < 1){
            return $this->error(Lang::get('edit fail'));
        }
        //只有当表号变了的时候需要添加新的表号，删除原来的表号
        if(($meterStart != $orders['meterStart']) || ($meterEnd != $orders['meterEnd'])){
            //删除原来表号
            if(($meterStart != '') && ($meterEnd != '')){
                $oldStart = "1".$meterStart;
                $oldEnd = "1".$meterEnd;
                $this->meterListDel($oldStart, $oldEnd, $oid);
            }else if($meterStart != ''){
                $where = array('meterNum'=>$meterStart, 'oid'=>$oid);
                $this->meters()->del($where);
            }

            //添加到表号
            //1.两个表号都有
            if(($orders['meterStart'] != '') && ($orders['meterEnd'] != '')){
                //$meterNums = $this->meterNumList($start, $end);
                $addMeter = $this->meterListAdd($start, $end, $oid);
            }else if($orders['meterStart'] != ''){
                //2.只有一个表号
                $meters['meterNum'] = $orders['meterStart'];
                $meters['oid'] = $oid;
                $addMeter = $this->meters()->add($meters, '');
            }
        }
        $page = input('param.page');
        $data = '?page='.$page;
        $url = url('Order/index').$data;
        return $this->success(Lang::get('edit success'), $url);

    }

    /**
     * json数据
     */
    public function order(){
        $oid = input('param.oid');
        $field = "oid,sid";
        $where = array('oid'=>$oid);
        $data = $this->orders()->select($field, $where);
        //var_dump($data);
        echo json_encode($data);
    }

    /**
     * 删除action
     */
    public function delOrd(){
        $auth = $this->auth('Order', 'del');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $oid = input('param.oid');
        $where = array('oid'=>$oid);
        $find = $this->orders()->findById($where);
        if(!$find){
            return $this->error(Lang::get('order unexist'));
        }
        $del = $this->orders()->del($where);
        if($del < 1){
            return $this->error(Lang::get('del fail'));
        }
        return $this->success(Lang::get('del success'), 'Order/index');
    }

    /**
     * 查看详单
     */
    public function view(){
        $this->authVerify();
        //国家，客户，基表型号，电子模块类型，制造商，生产负责人，发货状态都是下拉框选择
        $oid = input('param.oid');
        $order = $this->orders()->findById(array('oid'=>$oid));
        $order = $this->getOneJoinId($order);
        $this->assign('order', $order);
        $page = input('param.page');
        //var_dump($query);exit();
        $this->assign('currentPage', $page);
        return $this->fetch("ord/view");
    }

    /**
     * 根据输入的模块订单号导入： 模块数量，模块下单开始时间和模块下单结束时间
     */
    public function getModelNum(){
        $modelNum = input('param.modelNum');
        $modelNum = trim($modelNum);
        $oneModel = $this->sumQty($modelNum);
        echo json_encode($oneModel);
    }

    /**
     * 搜索
     */
    public function search(){
        $search = input('post.');
        $deliveryStatus = input('param.deliveryStatus');
        $meterNum = trim(input('param.meterNum'));
        $orderNum = trim(input('param.orderNum'));
        $modelNum = trim(input('param.modelNum'));
        $sid = input('param.sid');
        $cid = input('param.cid');
        $mfId = input('param.mfId');
        $ord = 'oid asc';
        $orders = $this->orders()->join($meterNum, $deliveryStatus, $sid, $cid, $orderNum, $modelNum, $mfId, $ord);
        //var_dump($orders);exit();
        $len = count($orders);
        //存在搜索的结果
        if($len >= 1){
            $orders = $this->getJoinId($orders);
        }
        $this->assignState();
        $this->assignClient();
        $this->assignManu();
        $this->page($orders);
        //var_dump($orders);exit();
        $this->assign('orders', $orders);
        return $this->fetch("ord/index");

    }

    /**
     * 统计报表
     */
    public function graph(){
        $this->statByState();
        $this->statByClient();
        $this->statByMeter();
        return $this->fetch("ord/graphys");
    }
    /**
     * 统计报表-按国家分类
     */
    public function statByState(){
        $auth = $this->auth('Order', 'graphs');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $rate = 6.5;
        $newOrders = $this->graphS($rate);
        //var_dump($newOrders);exit();
        $this->assign('graphyS', $newOrders);
        return $this->fetch("ord/graphys");
    }

    /**
     * 实时改变汇率，改变值--国家
     */
    public function jsonGraphS(){
        $auth = $this->auth('Order', 'graphs');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $rate = input('param.rate');
        $newOrders = $this->graphS($rate);
        echo $newOrders;
    }

    /**
     * 统计报表-按客户分类
     */
    public function statByClient(){
        $auth = $this->auth('Order', 'graphc');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $rate = 6.5;
        $newOrders = $this->graphC($rate);
        //var_dump($newOrder);exit();
        $this->assign('graphyC', $newOrders);
        return $this->fetch("ord/graphyc");
    }

    /**
     * 实时改变汇率，改变值--客户
     */
    public function jsonGraphC(){
        $auth = $this->auth('Order', 'graphc');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $rate = input('param.rate');
        $newOrders = $this->graphC($rate);
        echo $newOrders;

    }


    /**
     * 统计报表-按表型（基表型号？）分类
     */
    public function statByMeter(){
        $auth = $this->auth('Order', 'graphm');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $rate = 6.5;
        $newOrders = $this->graphM($rate);
        //var_dump($newOrders);exit();
        $this->assign('graphyM', $newOrders);
        return $this->fetch("ord/graphym");
    }

    /**
     * 实时改变汇率改变值--基表型号
     */
    public function jsonGraphM(){
        $auth = $this->auth('Order', 'graphm');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        $rate = input('param.rate');
        $newOrders = $this->graphM($rate);
        echo $newOrders;
    }


    /**
     * 导出订单excel
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function exportExcel(){
        $auth = $this->auth('Order', 'exportExcel');
        if(!$auth){
            return $this->error(Lang::get('no authority'));
        }
        Vendor('phpexcel.PHPExcel');
        Vendor('phpexcel.PHPExcel.IOFactory');
        Vendor('phpexcel.PHPExcel.Reader.Excel5');
        Vendor('phpexcel.PHPExcel.Writer.Excel2007');

        $phpexcel = new \PHPExcel();
        $phpexcel->getActiveSheet()->setTitle("订单表");
        //var_dump($phpCreate);exit();
        // 设置单元格高度
        // 所有单元格默认高度
        $phpexcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(25);
        // 第一行的默认高度
        $phpexcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
        // 垂直居中
        $phpexcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // 设置水平居中
        $phpexcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //Excel表格式
        $letter = array('A','B','C','D','E','F','G');
        //设置表头
        $tableheader = array('订单编号 ','客户','基表型号','订单数量','电子模块类型','生产状态','备注');
        //设置表头表格宽度
        /*$tablestyle = array(
            array('width'=>'15'),   //username
            array('width'=>'15'),   //password
            array('width'=>'15'),   //surname
            array('width'=>'20'),   //createTime
            array('width'=>'20'),   //updateTime
            array('width'=>'20'),   //status
            array('width'=>'20')
        );*/
        //填充表头信息
        for($i = 0;$i < count($tableheader);$i++) {
            $phpexcel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
            //$phpexcel->getActiveSheet()->getColumnDimension($letter[$i])->setWidth($tablestyle[$i]['width']);
        }
        //设置标题字体加粗和大小
        $phpexcel->getActiveSheet()->getStyle( 'A1:G1')->getFont()->setSize(12);
        $phpexcel->getActiveSheet()->getStyle( 'A1:G1')->getFont()->setBold(true);
        $phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $phpexcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true); //宽度自适应
        $phpexcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true); //宽度自适应
        $phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $phpexcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true); //宽度自适应
        $phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $phpexcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true); //宽度自适应
        //$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);

        //数据
        $field = 'oid,cid,meterId,orderQty,modelId,deliveryStatus,comments';
        $data = $this->orders()->select($field, '');
        //var_dump($data);exit();
        //数据处理
        $data = $this->setData($data);
        //var_dump($data);exit();
        //填充表格信息

        $rowIndex = 2;
        for ($i=0;$i<count($data);$i++) {

            $phpexcel->getActiveSheet()->setCellValue("A".$rowIndex,$data[$i]['oid']);
            $phpexcel->getActiveSheet()->setCellValue("B".$rowIndex,$data[$i]['client']);
            $phpexcel->getActiveSheet()->setCellValue("C".$rowIndex,$data[$i]['meterType']);
            $phpexcel->getActiveSheet()->setCellValue("D".$rowIndex,$data[$i]['orderQty']);
            $phpexcel->getActiveSheet()->setCellValue("E".$rowIndex,$data[$i]['modelType']);
            if($data[$i]['deliveryStatus'] == 0){
                $status = '已发货';
            }else if($data[$i]['deliveryStatus'] == 1){
                $status = '生产中';
            }else{
                $status = '未下单';
            }
            $phpexcel->getActiveSheet()->setCellValue("F".$rowIndex,$status);
            $phpexcel->getActiveSheet()->setCellValue(  "G".$rowIndex,$data[$i]['comments']);
            //设置默认行高
            $phpexcel->getActiveSheet($rowIndex)->getDefaultRowDimension()->setRowHeight(30);
            $rowIndex++;
        }



        //导出属性设置
        $date = date('Ymd', time());
        $outputFileName = "订单".$date.".xlsx";
        //require_once("Classes/PHPExcel/Writer/Excel2007.php");
        ob_end_clean();//清除缓冲区,避免乱码
        $objWriter = new \PHPExcel_Writer_Excel2007($phpexcel, 'Excel2007');
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outputFileName);
        header("Content-Transfer-Encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }


    ///////////////////////////

    /**
     * 表号验证，允许都为空，也可以结束表号单为空
     * @param $orders
     * @param $startLen
     * @param $endLen
     */
    private function verifyMeterNum($orders, $startLen, $endLen){
        //表号验证,允许为空
        ////1.长度10,,11,12,13;2.长度相等；3.前四位相同;4.都是数字；5.如果是13位，只保留前面12位。
        if($startLen > 0){
            if(($startLen < 10) || ($startLen > 13)){
                return $this->error(Lang::get('only 10 11 12 13 long'));
            }
            //结束表号是否为空
            if($endLen > 0){
                $this->verifyNum($orders['meterStart'], $orders['meterEnd']);
            }

        }else if($endLen > 0){
            return $this->error(Lang::get('only start unallowed'));
        }
    }

    /**
     * //修改订单之后删除之前添加的表号
     */
    private function delMeter($num){
        foreach ($num as $v){
            Db::startTrans();
            //var_dump($meterNums);exit();
            $where = array('mid'=>$v);
            try{
                $result = $this->meters()->del($where);
                Db::commit();
            }catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }
        }
    }



    /**
     * 表号的序列添加
     */
    private function meterListAdd($start, $end, $oid){

        $num = $this->meterNumList($start, $end);
        //var_dump($num);exit();
        foreach ($num as $v){
            $meterNums['meterNum'] = $v;
            $meterNums['oid'] = $oid;
            Db::startTrans();
            //var_dump($meterNums);exit();
            try{
                $this->meters()->add($meterNums, '');
                Db::commit();
            }catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }
        }

    }

    /**
     * 表号的序列删除
     * @param $start
     * @param $end
     * @param $oid
     */
    private function meterListDel($start, $end, $oid){
        $num = $this->meterNumList($start, $end);
        foreach ($num as $v){
            $meterNums['meterNum'] = $v;
            $meterNums['oid'] = $oid;
            Db::startTrans();
            //var_dump($meterNums);exit();
            $where = array('meterNum'=>$v, 'oid'=>$oid);
            try{
                $this->meters()->del($where);
                Db::commit();
            }catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }
        }
    }

    /**
     * 表号序列
     * @param $start
     * @param $end
     * @return mixed
     */
    private function meterNumList($start, $end){
        $startNum = floatval($start);
        $endNum = floatval($end);
        $intLength = intval($endNum-$startNum);
        $num = array();
        for ($i = 0;$i <= $intLength;$i++){
            $tem[$i] = $startNum + $i;
            $num[$i] = substr($tem[$i], 1);
        }
        return $num;
    }


    /**
     * 获取删除之前的表号列表，获取这些表号id
     */
    private function getMeterNumBeforeEdit($oid){
        $where = array('oid'=>$oid);
        //$field = "oid,numStartToEnd";
        $order = $this->orders()->findById($where);
        $mid = array();
        if($order['meterStart'] != ''){
            //结束表号也存在
            if($order['meterEnd'] != ''){
                $start = "1".$order['meterStart'];
                $end = "1".$order['meterEnd'];
                $num = $this->meterNumList($start, $end);
                foreach ($num as $ke=>$value){
                    $meter = $this->meters()->findById(array('meterNum'=>$value, 'oid'=>$oid));
                    $mid[$ke] = $meter['mid'];
                }
            }else{   //只有开始表号
                $findOne = $this->meters()->findById(array('meterNum'=>$order['meterStart'], 'oid'=>$oid));
                $mid = array($findOne['mid']);

            }
        }
        return $mid;
    }

    /**
     * 判断表号是否已存在
     */
    private function isExist($meterNum, $oid){
        foreach ($meterNum as $value){
            $where = array('meterNum'=>$value);
            $find = $this->meters()->findExist($where, $oid);
            if($find){
                return 1;
            }
        }
        return 0;
    }

    /**
     * 获取当前生成的订单编号
     * @param $orderNum
     */
    private function getOid($orderNum){
        $where = array('orderNum'=>$orderNum);
        $find = $this->orders()->findById($where);
        $oid = $find['oid'];
        return $oid;
    }


    private function setData($data){
        for($k=0;$k<count($data);$k++){
            //客户，基表型号，电子模块类型
            $clients = $this->clients()->findById(array('cid'=>$data[$k]['cid']));
            $meterTypes = $this->meterTypes()->findById(array('meterId'=>$data[$k]['meterId']));
            $modelTypes = $this->modelTypes()->findById(array('modelId'=>$data[$k]['modelId']));
            $data[$k]['client'] = $clients['client'];
            $data[$k]['meterType'] = $meterTypes['meterType'];
            $data[$k]['modelType'] = $modelTypes['modelType'];
            unset($data[$k]['cid']);
            unset($data[$k]['meterId']);
            unset($data[$k]['modelId']);
        }
        return $data;
    }

    /**
     * 获取剩余模块数量
     * @return array
     */
    private function sumQty($modelNum){
        $where = array('modelNum'=>$modelNum);
        //订单号升序
        $order = 'oid asc';
        $select = $this->orders()->orderSelect($where, $order);
        //var_dump($select);exit();
        $len = count($select);
        $sum = 0;
        //剩余量
        $surplus = 0;
        $oneModel = array(
            'modelQty' => $surplus,
            'modelStart' => '',
            'modelEnd' => '',
            'firstadd' =>'输入模块总数量'
        );
        if($len>0){
            //1.获取第一个订单号的模块数量和订单数量
            $nums = $select['0']['modelQty'];
            //$orderQty = $select['0']['orderQty'];
            //订单数量有的会有括号，需要做处理
            //$orderQty = $this->dealBraces($orderQty);
            //2.循环输出每一个订单的订单数量总和
            for ($i=0;$i<$len;$i++){
                $everyQty = $select[$i]['orderQty'];
                $everyQty = $this->dealBraces($everyQty);
                $sum += $everyQty;
            }
            //3.导出最后的剩余量（总的模块数量减去用掉的订单数量）
            $surplus = $nums - $sum;
            $firstadd = '';
            $start = $select['0']['modelStart'];
            $end = $select['0']['modelEnd'];
            //传给前端的数据
            $oneModel = array(
                'modelQty' => $surplus,
                'modelStart' => $start,
                'modelEnd' => $end,
                'firstadd' => $firstadd
            );
        }
        return $oneModel;
    }

    /**
     * 订单数量不是数字的处理（括号处理）
     */
    private function dealBraces($orderQty){
        //先判断是否包含括号(中英文格式都要判断)
        $start = 0;
        $end = 0;
        //1.英文开头
        $eStart = strpos($orderQty, '(');
        if($eStart){
            $start = $eStart;
        }
        //2.英文结束
        $eEnd = strpos($orderQty, ')');
        if($eEnd){
            $end = $eEnd;
        }
        //3.中文开头
        $zStart = strpos($orderQty, '（');
        if($zStart){
            //将中英文字符替换
            $orderQty = str_replace('（', '(', $orderQty);
            $start = strpos($orderQty, '(');
        }
        //4.中文结束
        $zEnd = strpos($orderQty, '）');
        if($zEnd){
            //将中英文字符替换
            $orderQty = str_replace('）', ')', $orderQty);
            $end = strpos($orderQty, ')');
        }
        if($start && $end){
            $len = $end-$start-1;
            $orderQty = substr($orderQty, $start+1, $len);
        }
        return $orderQty;
    }
    //获取json值
    private function graphS($rate){
        //默认rate用6.5表示
        //$rate = input('param.rate');
        //一维数组
        $orders = $this->groupSid($rate);
        $len = count($orders);
        //$newOrder = array();
        if($len > 0){
            $i=0;
            foreach ($orders as $sid=>$sumAmounts){
                $findSid = $this->state()->findById(array('sid'=>$sid));
                $newOrder[$i]['sid'] = $sid;
                $newOrder[$i]['state'] = $findSid['state'];
                $newOrder[$i]['value'] = number_format($sumAmounts, 2);
                //去除分隔符
                $newOrder[$i]['value'] = str_replace(',', '', $newOrder[$i]['value']);
                $i++;
            }
        }
        $newOrders = json_encode($newOrder);
        return $newOrders;
    }

    /**
     * 按国家计算总金额
     * @param $rate
     * @return array
     */
    private function groupSid($rate){
        $orders = $this->calSumByRate($rate);
        //var_dump($orders);
        $len = count($orders);
        $sameSid = array();
        $firstSid = $orders['0']['sid'];
        $sameSid[$firstSid]['0'] = $orders['0']['sumAmounts'];
        for($i=1;$i<$len;$i++){
            if($orders[$i]['sid'] == $orders[$i-1]['sid']){
                $sid = $orders[$i-1]['sid'];
                $sameSid[$sid][] = $orders[$i]['sumAmounts'];
            }else{
                $sid = $orders[$i]['sid'];
                $sameSid[$sid][]  = $orders[$i]['sumAmounts'];
            }
        }
        $sum = array();
        foreach ($sameSid as $sid=>$sumAmount){
            $sumSid = 0.00;
            for($j=0;$j<count($sumAmount);$j++){
                $sumSid += $sumAmount[$j];
            }
            $sum[$sid] = $sumSid;
        }
        //var_dump($sum);exit();
        return $sum;
    }
    //json--客户
    private function graphC($rate){
        //默认rate用6.5表示
        //$rate = input('param.rate');
        //一维数组
        $orders = $this->groupCid($rate);
        $len = count($orders);
        //$newOrder = array();
        if($len > 0){
            $i=0;
            foreach ($orders as $cid=>$sumAmounts){
                $findCid = $this->clients()->findById(array('cid'=>$cid));
                $newOrder[$i]['cid'] = $cid;
                $newOrder[$i]['client'] = $findCid['client'];
                $newOrder[$i]['value'] = number_format($sumAmounts, 2);
                //去除分隔符
                $newOrder[$i]['value'] = str_replace(',', '', $newOrder[$i]['value']);
                $i++;
            }
        }
        $newOrders = json_encode($newOrder);
        return $newOrders;
    }

    /**
     * 按客户计算总金额
     * @param $rate
     * @return array
     */
    private function groupCid($rate){
        $orders = $this->calSumByRate($rate);
        //var_dump($orders);
        $len = count($orders);
        $sameCid = array();
        $firstCid = $orders['0']['cid'];
        $sameCid[$firstCid]['0'] = $orders['0']['sumAmounts'];
        for($i=1;$i<$len;$i++){
            if($orders[$i]['cid'] == $orders[$i-1]['cid']){
                $cid = $orders[$i-1]['cid'];
                $sameCid[$cid][] = $orders[$i]['sumAmounts'];
            }else{
                $cid = $orders[$i]['cid'];
                $sameCid[$cid][]  = $orders[$i]['sumAmounts'];
            }
        }
        $sum = array();
        foreach ($sameCid as $cid=>$sumAmount){
            $sumCid = 0.00;
            for($j=0;$j<count($sumAmount);$j++){
                $sumCid += $sumAmount[$j];
            }
            $sum[$cid] = $sumCid;
        }
        //var_dump($sum);exit();
        return $sum;
    }
    //json--基表型号
    private function graphM($rate){
        //默认rate用6.5表示
        //$rate = input('param.rate');
        //一维数组
        $orders = $this->groupMeterId($rate);
        //var_dump($orders);exit();
        $len = count($orders);
        //$newOrder = array();
        if($len > 0){
            $i=0;
            foreach ($orders as $meterId=>$sumAmounts){
                $findMeterId = $this->meterTypes()->findById(array('meterId'=>$meterId));
                $pid = $findMeterId['pid'];
                //判断是否主类
                //如果是主类，直接输出
                //如果不是主类，找到主类meterId,分类的订单金额
                if($pid == 0){
                    $newOrder[$meterId]['meterId'] = $meterId;
                    $newOrder[$meterId]['meterType'] = $findMeterId['meterType'];
                    $newOrder[$meterId]['value']['0'] = number_format($sumAmounts, 2);
                    //去除分隔符
                    $newOrder[$meterId]['value']['0'] = str_replace(',','', $newOrder[$meterId]['value']['0']);
                }else{
                    $newOrder[$pid]['meterId'] = $pid;
                    $findNew = $this->meterTypes()->findById(array('meterId'=>$pid));
                    $newOrder[$pid]['meterType'] = $findNew['meterType'];
                    $newOrder[$pid]['value'][$meterId] = number_format($sumAmounts, 2);
                    //去除分隔符
                    $newOrder[$pid]['value'][$meterId] = str_replace(',', '', $newOrder[$pid]['value'][$meterId]);
                }

            }
            //var_dump($newOrder);exit();
            //继续做处理，计算各类订单value
            foreach ($newOrder as $meterId=>$value){
                $everyValue = $value['value'];
                //var_dump($everyValue);
                $tempSumValue = 0;
                foreach ($everyValue as $v){
                    $tempSumValue += $v;
                }
                //var_dump($tempSumValue);
                $value['value'] = number_format($tempSumValue, 2);
                //去除分隔符
                $value['value'] = str_replace(',', '', $value['value']);
                $tempOrder[] = $value;
            }
            //需要重新组合
            $newOrder = $tempOrder;
        }
        $newOrders = json_encode($newOrder);
        return $newOrders;
    }
    /**
     * 按基表型号计算总金额
     * @param $rate
     * @return array
     */
    private function groupMeterId($rate){
        $orders = $this->calSumByRate($rate);
        //var_dump($orders);exit();
        //var_dump($orders);
        $len = count($orders);
        //同一个基表型号属于一类
        $sameMeterId = array();
        $firstMeterId = $orders['0']['meterId'];
        $sameMeterId[$firstMeterId]['0'] = $orders['0']['sumAmounts'];
        for($i=1;$i<$len;$i++){
            if($orders[$i]['meterId'] == $orders[$i-1]['meterId']){
                $meterId = $orders[$i-1]['meterId'];
                $sameMeterId[$meterId][] = $orders[$i]['sumAmounts'];
            }else{
                $meterId = $orders[$i]['meterId'];
                $sameMeterId[$meterId][]  = $orders[$i]['sumAmounts'];
            }
        }
        $sum = array();
        foreach ($sameMeterId as $meterId=>$sumAmount){
            $sumMeterId = 0.00;
            for($j=0;$j<count($sumAmount);$j++){
                $sumMeterId += $sumAmount[$j];
            }
            $sum[$meterId] = $sumMeterId;
        }
        //var_dump($sum);exit();
        return $sum;
    }

    /**
     * 通过汇率计算总金额（人民币元）
     * @param $rate
     */
    private function calSumByRate($rate){
        //汇率转换成number
        $rate = floatval($rate);
        $field = 'oid,sid,cid,meterId,sumUnits,sumAmounts';
        $where = '';
        $order = 'oid';
        $orders = $this->orders()->orderSelect($where, $order);
        //var_dump($orders);exit();
        $len = count($orders);
        if($len > 0){
            for($i=0;$i<$len;$i++){
                if($orders[$i]['sumUnits'] == '$'){
                    $temp = floatval($orders[$i]['sumAmounts'] * $rate);
                    $orders[$i]['sumAmounts'] = $temp;

                }else{
                    $temp = floatval($orders[$i]['sumAmounts']);
                    $orders[$i]['sumAmounts'] = $temp;
                }
            }
        }
        //var_dump($orders);exit();
        return $orders;
    }
    /**
     * 去除数据前后空格
     * @param $orders
     * @return mixed
     */
    private function dealNull($orders){
        $orders['modelNum'] = trim($orders['modelNum']);
        $orders['orderNum'] = trim($orders['orderNum']);
        $orders['orderQty'] = trim($orders['orderQty']);
        $orders['sumAmounts'] = trim($orders['sumAmounts']);
        return $orders;
    }

}