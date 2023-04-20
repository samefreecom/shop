<?php
define('IN_CB', true);

include_once(dirname(__FILE__) . '/include/function.php');
/**
*
*/
class Lib_Barcodegen_image extends Lib_Base
{
    public function finish($str, $size = 1, $font_size = 8, $code = 'BCGcode128', $font_family = 'Arial.ttf', $filetype = 'PNG', $dpi = 72, $rotation = null)
    {
        $root = dirname(__FILE__);

        $get = array(
            'text' => $str
            , 'filetype' => $filetype
            , 'dpi' => $dpi
            , 'rotation' => $rotation
            , 'font_family' => $font_family
            , 'font_size' => $font_size
        );
        $codes = explode('-', $code);
        switch (count($codes)) {
            case 1:
                $code = current($codes);
                break;
            default:
                $code = $codes[0];
                $start = strtoupper($codes[1]);
                break;
        }
        $get['code'] = $code;
        if (isset($start)) {
            $get['start'] = $start;
        }
        $sizes = explode('-', $size);
        switch (count($sizes)) {
            case 1:
                $scale = current($sizes);
                break;
            default:
                $scale = $sizes[0];
                $thickness = strtoupper($sizes[1]);
                break;
        }
        $get['scale'] = $scale;
        if (isset($thickness)) {
            $get['thickness'] = $thickness;
        }
        if (!preg_match('/^[A-Za-z0-9]+$/', $code)) {
            return false;
        }

        // Check if the code is valid
        if (!file_exists($root . '/config' . DIRECTORY_SEPARATOR . $code . '.php')) {
            return false;
        }

        include_once($root . '/config' . DIRECTORY_SEPARATOR . $code . '.php');

        require_once($root . DIRECTORY_SEPARATOR . 'BCGColor.php');
        require_once($root . DIRECTORY_SEPARATOR . 'BCGBarcode.php');
        require_once($root . DIRECTORY_SEPARATOR . 'BCGDrawing.php');
        require_once($root . DIRECTORY_SEPARATOR . 'BCGFontFile.php');
        if (isset($classFile)) {
            $GLOBALS['classFile'] = $classFile;
        } else {
            $classFile = $GLOBALS['classFile'];
        }
        if (isset($className)) {
            $GLOBALS['className'] = $className;
        } else {
            $className = $GLOBALS['className'];
        }
        if (isset($baseClassFile)) {
            $GLOBALS['baseClassFile'] = $baseClassFile;
        } else {
            $baseClassFile = $GLOBALS['baseClassFile'];
        }
        if (isset($codeVersion)) {
            $GLOBALS['codeVersion'] = $codeVersion;
        } else {
            $codeVersion = $GLOBALS['codeVersion'];
        }
        if (!include_once($root . DIRECTORY_SEPARATOR . $classFile)) {
            return false;
        }

        include_once($root . '/config' . DIRECTORY_SEPARATOR . $baseClassFile);

        $filetypes = array('PNG' => BCGDrawing::IMG_FORMAT_PNG, 'JPEG' => BCGDrawing::IMG_FORMAT_JPEG, 'GIF' => BCGDrawing::IMG_FORMAT_GIF);

        $drawException = null;
        try {
            $color_black = new BCGColor(0, 0, 0);
            $color_white = new BCGColor(255, 255, 255);

            $code_generated = new $className();

            if (function_exists('baseCustomSetup')) {
                baseCustomSetup($code_generated, $get);
            }

            if (function_exists('customSetup')) {
                customSetup($code_generated, $get);
            }

            $code_generated->setScale($size);
            $code_generated->setBackgroundColor($color_white);
            $code_generated->setForegroundColor($color_black);

            if (is_string($str)) {
                $text = convertText($str);
                $code_generated->parse($text);
            }
        } catch(Exception $exception) {
            $drawException = $exception;
        }

        $drawing = new BCGDrawing('', $color_white);
        if($drawException) {
            $drawing->drawException($drawException);
        } else {
            $drawing->setBarcode($code_generated);
            $drawing->setRotationAngle($rotation);
            $drawing->setDPI($dpi ? null : max(72, min(300, intval($dpi))));
            $drawing->draw();
        }

        switch ($filetype) {
            case 'PNG':
                header('Content-Type: image/png');
                break;
            case 'JPEG':
                header('Content-Type: image/jpeg');
                break;
            case 'GIF':
                header('Content-Type: image/gif');
                break;
        }
        $drawing->finish($filetypes[$filetype]);
    }
}
?>