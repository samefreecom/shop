<?php
include ROOT_CORE . '/application/lib/PHPExcel.php';
class Lib_Excel extends Lib_Base
{
    private $excelList = [];
    private function htmlspecialchars_decode ($str) {
        return strtr($str, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
    }
    public function read($file, $index = 0)
    {
        $importArray = [];
        $key = md5_file($file);
        /** @var $objPHPExcel PHPExcel */
        $objPHPExcel = null;
        if (!isset($this->excelList[$key])) {
            $this->excelList[$key] = \PHPExcel_IOFactory::load($file);
        }
        $objPHPExcel = $this->excelList[$key];
        if (is_numeric($index)) {
            $sheet = $objPHPExcel->getSheet($index);
        } else {
            $sheet = $objPHPExcel->getSheetByName($index);
        }
        $allRow = $sheet->getHighestRow(); // 取得总行数
        $allColumn = $sheet->getHighestColumn(); //取得总列数

        //每行的数据
        $val = array();
        //第一行代变列头，这里做key值
        $header = array();

        for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
            $header_address = $currentColumn."1";
            $header_cell = $sheet->getCell($header_address);
            $header_cell = $header_cell->getValue();
            $header[$currentColumn] = $this->htmlspecialchars_decode($header_cell); //处理了一下字符串
        }

        for($currentRow=2;$currentRow<=$allRow;$currentRow++){
            for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
                $address=$currentColumn.$currentRow;//数据坐标:A1,B1....
                $cell =$sheet->getCell($address);
                $cell = $cell->getValue();
                $val[$header[$currentColumn]] = $this->htmlspecialchars_decode($cell); //处理了一下字符串

            }
            $importArray[] = $val;
        }
        array_filter($importArray);
        return $importArray;
    }

    public function write($file, $list, $mapHead = [])
    {
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $keys = array_keys($list[0]);
        $basic = 'A';
        $basicMap = [];
        for ($i = 0, $len = count($keys); $i < $len; $i++) {
            $head = !empty($mapHead[$keys[$i]]) ? $mapHead[$keys[$i]] : $keys[$i];
            $basicMap[$keys[$i]] = $basic;
            $objPHPExcel->getActiveSheet()->SetCellValue($basic . '1', $head);
            $basic++;
        }
        $j = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($list as $value) {
            foreach ($value as $k => $v) {
                if (is_numeric(strpos($v, '/public/static/')) && is_file(ROOT . $v)) {
                    try {
                        $objDrawing  = new PHPExcel_Worksheet_Drawing();
                        // 获取图片地址
                        $objDrawing->setPath(ROOT . $v);
                        // 设置图片存放在表格的位置
                        $objDrawing->setCoordinates($basicMap[$k] . $j);
                        // 设置表格宽度
                        $objActSheet->getColumnDimension($basicMap[$k])->setWidth(20);
                        // 设置表格高度
                        $objActSheet->getRowDimension($j)->setRowHeight(60);
                        // 设置图片宽
                        //$objDrawing->setWidth(80);
                        // 设置图片高
                        $objDrawing->setHeight(60);
                        // 设置X方向偏移量
                        $objDrawing->setOffsetX(10);
                        // 设置Y方向偏移量
                        $objDrawing->setOffsetY(10);
                        $objDrawing->setWorksheet($objActSheet);
                    } catch (\Exception $e) {
                    }
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit($basicMap[$k] . $j, $v, PHPExcel_Cell_DataType::TYPE_STRING);
                }
            }
            $j++;
        }
        /** @var PHPExcel_Writer_Excel2007 PHPExcel */
        $objPHPExcelWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $objPHPExcelWriter->save($file);
    }
}