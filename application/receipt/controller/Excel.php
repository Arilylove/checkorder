<?php
/**
 * Created by IntelliJ IDEA.
 * User: HXL
 * Date: 2018/5/14
 * Time: 18:47
 */
namespace app\receipt\controller;

use think\Controller;
use think\Db;
class Excel extends Controller{

    /**
     * 导出demo
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function exportAll()
    {
        //获取年月和考勤天数
        $date = date('Ymd-His', time());
        //$days = $this->_GET('days');

        //require_once("Classes/PHPExcel.php");
        //include("Classes/PHPExcel/IOFactory.php");
        Vendor('phpexcel.PHPExcel');
        Vendor('phpexcel.PHPExcel.IOFactory');
        Vendor('phpexcel.PHPExcel.Reader.Excel5');
        Vendor('phpexcel.PHPExcel.Writer.Excel2007');
        $temPath = ROOT_PATH.'public'.DS."model".DS."model.xls";
        //var_dump($temPath);exit();
        //检查文件路径
        if(!file_exists($temPath)){
            return $this->error('模板不存在');
        }
        //加载模板
        $phpCreate =  \PHPExcel_IOFactory::createReader("Excel5");
        //var_dump($phpCreate);exit();
        $phpexcel = $phpCreate->load($temPath);
        //var_dump($phpCreate);exit();
        //数据
        //$groupMember  = Db::table('admin')->field('adId,username,surname,password,createTime,status')->order('adId asc')->select();
        $groupMember = array(
            '0'=>array(
                '1',
                'test1',
                'pcs',
                '0.2',
                '18.00'
            ),
            '1'=>array(
                '2',
                'test1',
                'pcs',
                '200',
                '18.00'
            ),
            '2'=>array(
                '3',
                'test1',
                'pcs',
                '200',
                '18.00'
            ),
            '3'=>array(
                '4',
                'test1',
                'pcs',
                '200',
                '18.00'
            )
        );
        $rowIndex = 14;
        $len = count($groupMember);
        //数据超过3行，需要
        if($len > 2){
            //分离单元格
            //$phpexcel->getActiveSheet()->unmergeCells('A16:F16');
            $phpexcel->getSheet()->insertNewRowBefore(16, $len-2);
        }
        //var_dump($groupMember);exit();
        //填充可写的数据
        $sum = 0;
        foreach($groupMember as $k=>$item){
            //一览表写入数据
            $phpexcel->getActiveSheet()->setCellValue('A'.$rowIndex, $item['0']);
            $phpexcel->getActiveSheet()->setCellValue('B'.$rowIndex, $item['1']);
            $phpexcel->getActiveSheet()->setCellValue('C'.$rowIndex, $item['2']);
            $phpexcel->getActiveSheet()->setCellValue('D'.$rowIndex, $item['3']);
            $phpexcel->getActiveSheet()->setCellValue('E'.$rowIndex, 'US$'.$item['4']);
            $item4 = number_format($item['3']*$item['4'], 2, '.','');
            $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, 'US$'.$item4);
            $sum += $item['3']*$item['4'];
            //设置行高
            $phpexcel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight(24);
            $rowIndex++;
        }
        //合并单元格
        //$phpexcel->getActiveSheet()->mergeCells('A18:E22');
        //新的一行数据
        if($len > 2){
            $phpexcel->getSheet()->insertNewRowBefore(19+$len-3, $len-2);
        }
        $rowIndex = $rowIndex+1;
        foreach($groupMember as $k=>$item){
            $phpexcel->getActiveSheet()->setCellValue('A'.$rowIndex, $item['0']);
            $phpexcel->getActiveSheet()->setCellValue('B'.$rowIndex, $item['1']);
            $phpexcel->getActiveSheet()->setCellValue('C'.$rowIndex, $item['2']);
            $phpexcel->getActiveSheet()->setCellValue('D'.$rowIndex, $item['3']);
            $phpexcel->getActiveSheet()->setCellValue('E'.$rowIndex, 'US$'.$item['4']);
            $item5 = number_format($item['3']*$item['4'], 2, '.','');
            $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, 'US$'.$item5);
            $sum += $item['3']*$item['4'];
            //设置行高
            $phpexcel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight(24);
            $rowIndex++;
        }

        //如果数据少于行数，需要删除多余的行
        $sum = number_format($sum, 2, '.', '');
        //var_dump($rowIndex);exit();
        $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, "US$".$sum);
        //导出属性设置
        $date = str_replace("/","_",$date);
        $outputFileName = "发票".$date.".xlsx";
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

    /**
     * 导出excel封装
     * @param $profomaData  发票收方信息array
     * @param $plasticData  plastic信息array
     * @param $softwareData software信息array
     * @param $filename 发票名称
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function export($profomaData, $plasticData, $softwareData, $filename)
    {
        /*var_dump($profomaData);
        var_dump($plasticData);
        var_dump($softwareData);exit();*/
        $date = date('Ymd-His', time());
        Vendor('phpexcel.PHPExcel');
        Vendor('phpexcel.PHPExcel.IOFactory');
        Vendor('phpexcel.PHPExcel.Reader.Excel5');
        Vendor('phpexcel.PHPExcel.Writer.Excel2007');
        $temPath = ROOT_PATH.'public'.DS."model".DS."model.xls";
        //var_dump($temPath);exit();
        //检查文件路径
        if(!file_exists($temPath)){
            return $this->error('模板不存在');
        }
        //加载模板
        $phpCreate =  \PHPExcel_IOFactory::createReader("Excel5");
        //var_dump($phpCreate);exit();
        $phpexcel = $phpCreate->load($temPath);
        //var_dump($phpCreate);exit();
        //1.收方数据
        $phpexcel->getActiveSheet()->setCellValue('B9', $profomaData['to']);
        $phpexcel->getActiveSheet()->setCellValue('B10', $profomaData['nameNum']);
        $phpexcel->getActiveSheet()->setCellValue('B11', $profomaData['email']);
        $phpexcel->getActiveSheet()->setCellValue('F9', "No.:".$profomaData['no']);
        $phpexcel->getActiveSheet()->setCellValue('F10', "DATE:".$profomaData['date']);
        //2.plastic数据
        $rowIndex = 14;
        $plasticDataLen = count($plasticData);
        //数据超过2行，需要插入新的单元格
        if($plasticDataLen > 2){
            $phpexcel->getSheet()->insertNewRowBefore(16, $plasticDataLen-2);
        }/*else if($plasticDataLen < 2){  //只有一行数据
            //合并单元格
            $phpexcel->getActiveSheet()->mergeCells('A14:F15');
        }*/
        //var_dump($groupMember);exit();
        //填充可写的数据
        $sum = 0; //计算数据总和
        //数据编号
        $i = 1;
        foreach($plasticData as $k=>$item){
            $item['no'] = $i;
            //一览表写入数据
            $phpexcel->getActiveSheet()->setCellValue('A'.$rowIndex, $item['no']);
            $phpexcel->getActiveSheet()->setCellValue('B'.$rowIndex, $item['specification']);
            $phpexcel->getActiveSheet()->setCellValue('C'.$rowIndex, $item['unit']);
            $phpexcel->getActiveSheet()->setCellValue('D'.$rowIndex, $item['qty']);
            $item['price'] = number_format($item['price'], 2, '.','');
            $phpexcel->getActiveSheet()->setCellValue('E'.$rowIndex, 'US$'.$item['price']);
            $item4 = number_format($item['qty']*$item['price'], 2, '.','');
            $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, 'US$'.$item4);
            $sum += $item['qty']*$item['price'];
            //设置行高
            $phpexcel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight(44);
            $rowIndex++;
            $i++;
        }
        //3.software数据
        //新的一行数据
        if($plasticDataLen < 2){
            $rowIndex = $rowIndex +2;
        }else{
            $rowIndex = $rowIndex+1;
        }
        $softDataLen = count($softwareData);
        if($softDataLen > 2){
            $phpexcel->getSheet()->insertNewRowBefore($rowIndex+2, $softDataLen-2);
        }/*else if($softDataLen < 2){
            //合并单元格
            $phpexcel->getActiveSheet()->mergeCells('A'.$rowIndex.':F'.($rowIndex+1));
        }*/
        //数据编号
        $j = 1;
        foreach($softwareData as $k=>$item){
            $item['no'] = $j;
            $phpexcel->getActiveSheet()->setCellValue('A'.$rowIndex, $item['no']);
            $phpexcel->getActiveSheet()->setCellValue('B'.$rowIndex, $item['specification']);
            $phpexcel->getActiveSheet()->setCellValue('C'.$rowIndex, $item['unit']);
            $phpexcel->getActiveSheet()->setCellValue('D'.$rowIndex, $item['qty']);
            $item['price'] = number_format($item['price'], 2, '.','');
            $phpexcel->getActiveSheet()->setCellValue('E'.$rowIndex, 'US$'.$item['price']);
            $item5 = number_format($item['qty']*$item['price'], 2, '.','');
            $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, 'US$'.$item5);
            $sum += $item['qty']*$item['price'];
            //设置行高
            $phpexcel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight(44);
            $rowIndex++;
            $j++;
        }

        $sum = number_format($sum, 2, '.', '');
        //var_dump($rowIndex);exit();
        //如果数据少于行数
        if($softDataLen < 2){
            $phpexcel->getActiveSheet()->setCellValue('F'.($rowIndex+1), "US$".$sum);
        }else{
            $phpexcel->getActiveSheet()->setCellValue('F'.$rowIndex, "US$".$sum);
        }
        //导出属性设置
        //$date = str_replace("/","_",$date);
        $outputFileName = $filename."-发票.xlsx";
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


    ////////////////////////////////////分割线////////////////////////////////////////////////////////////////////////////////
    /**
     * 导出EXCEL
     * date:2016-10-31
     * author:yuan
     */
    public function exp(){//导出Excel
        $time = time();
        $xlsName  = "用户信息".$time;
        $header = ["用户编号","用户名","用户姓名","密码","创建日期"];
        $xlsCell  = array(
            array('adId'),
            array('username'),
            array('surname'),
            array('password'),
            array('createTime'),
            array('status')
        );
        $xlsData  = Db::table('admin')->field('adId,username,surname,password,createTime')->order('adId asc')->select();
        //时间戳转日期格式
        /*var_dump($xlsData);exit();
        foreach($xlsData as $key=>$val){
            $xlsData[$key]['createTime'] = date('Y-m-d H:i:s', $xlsData[$key]['createTime']);
        }*/
        $this->exportExcel($xlsData, $header, $xlsName);
    }
    /**
     * @creator Jimmy
     * @data 2018/1/05
     * @desc 数据导出到excel(csv文件)
     * @param $filename 导出的csv文件名称 如date("Y年m月j日").'-test.csv'
     * @param array $tileArray 所有列名称
     * @param array $dataArray 所有列数据
     */
    public  function exportToExcel($filename, $tileArray=[], $dataArray=[]){
        ini_set('memory_limit','512M');
        ini_set('max_execution_time',0);
        ob_end_clean();
        ob_start();
        header("Content-Type: text/csv");
        header("Content-Disposition:filename=".$filename.".xlsx");
        $fp=fopen('php://output','w');
        fwrite($fp, chr(0xEF).chr(0xBB).chr(0xBF));//转码 防止乱码(比如微信昵称(乱七八糟的))
        fputcsv($fp,$tileArray);
        $index = 0;
        foreach ($dataArray as $item) {
            if($index==1000){
                $index=0;
                ob_flush();
                flush();
            }
            $index++;
            fputcsv($fp,$item);
        }

        ob_flush();
        flush();
        ob_end_clean();
    }
}