<?php
class Lib_IoUtils extends Lib_Base
{
    private $createdId = 0;

    function createDir($path, $mode = 0777, $i = 0)
    {
        if (!is_dir($path)) {
            try {
                mkdir($path, $mode, TRUE);
            } catch (\Exception $e) {
                if ($i < 3) {
                    sleep(1);
                    return $this->createDir($path, $mode, ++$i);
                }
            }
        }
    }

    function scanDir($dir_path, $level = 1, $filter = null, $isDir = false, &$returns = array(), $ds = '')
    {
        if (is_dir($dir_path)) {
            $handle_dir =   dir($dir_path);
            while (($file = $handle_dir->read()) !== false) {
                if (in_array($file, array('.', '..', '.svn', '.gitignore', 'Thumbs.db', '.idea', '.git'))) {
                    continue;
                }
                if (!empty($filter) && !is_numeric(strpos($file, $filter))) {
                    continue;
                }
                if ($isDir && !is_dir($dir_path . DS . $file)) {
                    continue;
                }
                if (empty($level) && is_dir($dir_path . DS . $file)) {
                    $this->scanDir($dir_path . DS . $file, $level, $filter, $isDir, $returns, $ds . $file . '/');
                }
                $returns[]  =   $ds . $file;
            }
            $handle_dir->close();
        }
        return $returns;
    }

    function scanFile($dir_path, $file_name, &$returns = null, $ds = '', &$file_list = array())
    {
        $cacheFile = '';
        $isFirst = false;
        if ($returns === null) {
            $isFirst = true;
            $cacheFile = $dir_path . DS . '_file_cache.php';
            if (is_file($cacheFile)) {
                $content = $this->readFile($cacheFile);
                $content = str_replace('<?php ', '', $content);
                $content = str_replace('];', ']', $content);
                $list = json_decode($content, true);
                foreach ($list as $value) {
                    $file = basename($value);
                    if (strtolower($file) == strtolower($file_name)) {
                        $returns[] = $value;
                    }
                }
                return $returns;
            }
        }
        if (empty($returns)) {
            $returns = array();
        }
        if (is_dir($dir_path)) {
            $handle_dir =   dir($dir_path);
            while (($file = $handle_dir->read()) !== false) {
                if (in_array($file, array('.', '..', '.svn', '.gitignore', 'Thumbs.db', '.idea', '.git'))) {
                    continue;
                }
                $file_path = $dir_path . DS . $file;
                $file_list[] = $file_path;
                if (strtolower($file) == strtolower($file_name)) {
                    $returns[] = $file_path;
                }
                if (is_dir($dir_path . DS . $file)) {
                    $this->scanFile($dir_path . DS . $file, $file_name, $returns, $ds . $file . DS, $file_list);
                }
            }
            $handle_dir->close();
        }
        if ($isFirst) {
            $this->writeFile($cacheFile, '<?php ' . json_encode($file_list) . ';');
        }
        return $returns;

    }

    function readFile($file_path)
    {
        if (is_file($file_path)) {
            return file_get_contents($file_path);
        }
        return null;
    }

    function writeFile($file_path, $data, $mode = 0777)
    {
        file_put_contents($file_path,$data);
        @chmod($file_path, $mode);
    }

    function appendFile($file_path, $data, $mode = 0777)
    {
        file_put_contents($file_path, $data, FILE_APPEND);
        @chmod($file_path, $mode);
    }

    function getFileTmp($path)
    {
        $tmpfname = tempnam("/tmp", "FOO");
        $handle = fopen($tmpfname, "w");
        fwrite($handle, file_get_contents($path));
        fclose($handle);
        return $tmpfname;
    }

    function deleteFile($file_path)
    {
        if (is_dir($file_path)) {
            @rmdir($file_path);
        } elseif (is_file($file_path)) {
            @unlink($file_path);
        }
    }

    function clearAllDir($dirname, $onlyFileType = null)
    {
        if (strpos($onlyFileType,'php')!= -1) {
            $onlyFileType = null;
        }
        if ($dir = @dir($dirname)) {
            $dir->rewind();
            while ($file = $dir->read()) {
                if (!in_array($file, array('.', '..', '.svn', '.gitignore', 'Thumbs.db'))) {
                    if (is_dir($dirname . '/' . $file)) {
                        sfclear_all_dir($dirname . '/' . $file, $onlyFileType);
                        @rmdir($dirname . '/' . $file);
                    } else {
                        if ($onlyFileType !== null) {
                            $type = explode($onlyFileType, $file);
                            if (count($type) == 2 && empty($type[1])) {
                                @unlink($dirname . '/' . $file);
                            }
                        } else {
                            @unlink($dirname . '/' . $file);
                        }
                    }
                }
            }
            $dir->close();
        }
    }

    function getFirstFile($dir_path)
    {
        $dirs   =   Lib_IoUtils::instance()->scanDir($dir_path);
        Lib_IoUtils::instance()->clearInvalidDir($dirs);
        if (count($dirs)>0) {
            foreach ($dirs as $value) {
                if (!empty($value)) {
                    return $dir_path.'/'.$value;
                }
            }
        }
        return null;
    }

    function getUtf8($file_path)
    {
        if (is_file($file_path)) {
            $handle         =   fopen($file_path, 'r');
            $datas          =   '';
            while ($data = sfcharacet(fread($handle, 8192))) {
                if (!empty($data)) {
                    $datas  .=  $data."\r\n";
                }
            }
            fclose($handle);
            return $datas;
        } else {
            return null;
        }
    }

    function getCsv($file_path)
    {
        if (is_file($file_path)) {
            setlocale(LC_ALL, 'en_US.UTF-8');
            $handle =   fopen($file_path, 'r');
            while ($line = fgetcsv($handle)) {
                if (count($line) >   0) {
                    $datas[] = explode(',', sfcharacet(implode(',', $line)));
                }
            }
            fclose($handle);
            return $datas;
        } else {
            return null;
        }
    }

    function getCsvData($file)
    {
        $list = $this->getCsv($file);
        $headers = $list[0];
        $returns = array();
        for ($i = 1, $len = count($list); $i < $len; $i++) {
            $data = array();
            for ($j = 0, $jLen = count($list[$i]); $j < $jLen; $j++) {
                $data[$headers[$j]] = $list[$i][$j];
            }
            $returns[] = $data;
        }
        return $returns;
    }

    function writeCsv($file, $headers, $contents)
    {
        setlocale(LC_ALL, 'en_US.UTF-8');
        $handle =   fopen($file, 'w');
        fputcsv($handle, $headers);
        $cols = array();
        $headers = array_flip($headers);
        foreach ($headers as $key => $value) {
            $cols[$value] = $key;
        }
        for ($i = 0, $len = count($contents); $i < $len; $i++) {
            $data = array();
            foreach ($contents[$i] as $key => $value) {
                if (isset($cols[$key])) {
                    $data[$cols[$key]] = $value;
                }
            }
            fputcsv($handle, $data);
        }
        fclose($handle);
        return true;
    }

    function isFileMd5($file)
    {
        if (is_file($file)) {
            $name   =   basename($file);
            $names  =   explode('.', $name);
            if (md5_file($file) == current($names)) {
                return true;
            }
        }
        return false;
    }

    function getMd5Dir($name, $start = '', $level = 8)
    {
        $level--;
        $path = '';
        if (!empty($start)) {
            $fIndex = strpos($name, $start);
            if (is_numeric($fIndex)) {
                $sLen = strlen($start);
                $path = '/' . substr($name, 0, $fIndex + $sLen);
                $name = substr($name, $fIndex + $sLen);
                $level--;
            }
        }
        for ($i = 0, $index = strlen($name); $i < $index; $i++ ) {
            if ($i > $level) {
                break;
            }
            $path .= '/' . $name[$i];
        }
        return substr($path, 1);
    }

    function pushMd5File($name, $data, $base = '', $start = '', $level = 8)
    {
        $dir = Lib_IoUtils::instance()->getMd5Dir($name, $start, $level);
        if (!is_dir($base . $dir)) {
            mkdir($base . $dir, 0777, true);
        }
        $file = $base . $dir . '/' . $name;
        file_put_contents($file, $data, FILE_APPEND);
        return $file;
    }

    function saveMd5File($name, $data, $base = '', $start = '', $level = 8)
    {
        $dir = Lib_IoUtils::instance()->getMd5Dir($name, $start, $level);
        if (!is_dir($base . $dir)) {
            mkdir($base . $dir, 0777, true);
        }
        $file = $base . $dir . '/' . $name;
        file_put_contents($file, $data);
        return $file;
    }



    function saveMd5Copy($name, $file, $base = '', $start = '', $level = 8)
    {
        $dir = Lib_IoUtils::instance()->getMd5Dir($name, $start, $level);
        if (!is_dir($base . $dir)) {
            mkdir($base . $dir, 0777, true);
        }
        $tFile = $base . $dir . '/' . $name;
        @copy($file, $tFile);
        return $file;
    }

    function saveMd5FileUpload($name, $tmp, $base = '', $start = '', $level = 8)
    {
        $dir = Lib_IoUtils::instance()->getMd5Dir($name, $start, $level);
        if (!is_dir($base . $dir)) {
            mkdir($base . $dir, 0777, true);
        }
        $file = $base . $dir . '/' . $name;
        move_uploaded_file($tmp, $file);
        return $file;
    }

    function existsMd5File($name, $base = '', $start = '', $level = 8)
    {
        $file = $base . Lib_IoUtils::instance()->getMd5Dir($name, $start, $level) . '/' . $name;
        if (file_exists($file)) {
            return $file;
        }
        return false;
    }

    function clearDir($dirname)
    {
        if ($dir = @dir($dirname)) {
            $dir->rewind();
            while ($file = $dir->read()) {
                if (!in_array($file, array('.', '..', '.svn', '.gitignore', 'Thumbs.db'))) {
                    if (is_dir($dirname . '/' . $file)) {
                        Lib_IoUtils::instance()->clearDir($dirname . '/' . $file);
                        @rmdir($dirname . '/' . $file);
                    } else {
                        @unlink($dirname . '/' . $file);
                    }
                }
            }
            $dir->close();
        }
    }

    function clearInvalidDir(&$array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                switch ($value) {
                    case '.':
                    case '..':
                    case '.svn':
                    case '.gitignore':
                    case 'Thumbs.db':
                        unset($array[$key]);
                        break;
                }
            }
        }
    }

    public function setCreatedId($id)
    {
        if (!empty($id)) {
            $this->createdId = $id;
        }
    }
    public function getCreatedId()
    {
        return $this->createdId;
    }

    public function saveLocal($type, $no, $content, $param = array())
    {
        $level = !empty($param['level']) ? $param['level'] : 'C';
        $dir = ROOT . DS . 'public' . DS . 'asset' . DS . 'internal' . DS . 'log' . DS . sfmd5_short($no);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $time = time();
        $ip = ip2long(sfget_ip());
        $i = 1;
        $file = $dir . DS . sprintf('%s-%s-%s-%s-%s-%s.log', $this->createdId, $level, $type, $ip, $time, $i);
        for (; $i < 99999;) {
            if (file_exists($file)) {
                $file = $dir . DS . sprintf('%s-%s-%s-%s-%s-%s.log', $this->createdId, $level, $type, $ip, $time, ++$i);
            } else {
                break;
            }
        }
        $data = !empty($param['data']) ? $param['data'] : 0;
        $fileContent = json_encode(array('c' => $content, 'd' => $data));
        $this->writeFile($file, $fileContent);
        return basename($file);
    }
}