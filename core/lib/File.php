<?php

class File
{

    const UPLOAD_ERR_MOVE = 210;
    const UPLOAD_ERR_UNEXPECTED = 211;
    const CLASSNAME = 'FILE';

    public static function tail($filename, $lines = 10)
    {
        $data = '';
        $fp = fopen($filename, "r");
        $block = 4096;
        $max = filesize($filename);

        for ($len = 0; $len < $max; $len += $block)
        {
            $seekSize = ($max - $len > $block) ? $block : $max - $len;
            fseek($fp, ($len + $seekSize) * -1, SEEK_END);
            $data = fread($fp, $seekSize) . $data;

            if (substr_count($data, "\n") >= $lines + 1)
            {
                /* Make sure that the last line ends with a '\n' */
                if (substr($data, strlen($data) - 1, 1) !== "\n")
                {
                    $data .= "\n";
                }

                preg_match("!(.*?\n){" . $lines . "}$!", $data, $match);
                fclose($fp);
                return $match[0];
            }
        }
        fclose($fp);
        return $data;
    }

    public static function getSubDirNames($directory)
    {
        $out = array();
        $files = glob($directory . "/*");
        foreach ($files as $file)
            if (is_dir($file))
                $out[] = $file;

        return $out;
    }

    public static function getFileNames($directory)
    {
        $out = array();
        $files = glob($directory . "/*");
        foreach ($files as $file)
        {
            if (!is_dir($file))
            {
                $out[] = $file;
            }
        }
        return $out;
    }

    public static function uploadFile($file, $dest_path, $uPublicPath = true)
    {
        $baseDir = $uPublicPath ? Settings::$projectFullPublicPath : Settings::$projectFullPath;

        if (is_array($file))
        {
            $arr = $file;
        }
        elseif (isset($_FILES[$file]))
        {
            $arr = $_FILES[$file];
        }
        else
        {
            Log::log(self::CLASSNAME, 'Wrong parameters supplied');
            return false;
        }

        $dest_path = $baseDir . DIRECTORY_SEPARATOR . $dest_path;
        $arr['name'] = (array) $arr['name'];
        $arr['type'] = (array) $arr['type'];
        $arr['tmp_name'] = (array) $arr['tmp_name'];
        $arr['error'] = (array) $arr['error'];
        $arr['size'] = (array) $arr['size'];

        $errors = array();

        foreach ($arr['name'] as $key => $name)
        {
            if ($arr['error'][$key] === UPLOAD_ERR_INI_SIZE)
            {
                $errors[$key] = self::uploadError(UPLOAD_ERR_INI_SIZE);
                continue;
            }

            if ($arr['error'][$key] === UPLOAD_ERR_FORM_SIZE)
            {
                $errors[$key] = self::uploadError(UPLOAD_ERR_FORM_SIZE);
                continue;
            }

            if ($arr['error'][$key] === UPLOAD_ERR_PARTIAL)
            {
                $errors[$key] = self::uploadError(UPLOAD_ERR_PARTIAL);
                continue;
            }

            if ($arr['error'][$key] === UPLOAD_ERR_NO_FILE || !is_uploaded_file($arr['tmp_name'][$key]) || $arr['size'][$key] == 0)
            {
                $errors[$key] = self::uploadError(UPLOAD_ERR_NO_FILE);
                continue;
            }

            if ($arr['error'][$key] === UPLOAD_ERR_NO_TMP_DIR)
            {
                $errors[$key] = self::uploadError(UPLOAD_ERR_NO_TMP_DIR);
                continue;
            }

            if ($arr['error'][$key] === UPLOAD_ERR_CANT_WRITE)
            {
                $errors[$key] = self::uploadError(UPLOAD_ERR_CANT_WRITE);
                continue;
            }

            if ($arr['error'][$key] === UPLOAD_ERR_EXTENSION)
            {
                $errors[$key] = self::uploadError(UPLOAD_ERR_EXTENSION);
                continue;
            }

            if (is_dir($dest_path))
            {
                if (!is_writable($dest_path))
                {
                    $errors[$key] = self::uploadError(UPLOAD_ERR_CANT_WRITE);
                    continue;
                }
                $new_name = $dest_path . DIRECTORY_SEPARATOR . $name;
            }
            else
            {
                $new_name = $dest_path;
            }

            if (!move_uploaded_file($arr['tmp_name'][$key], $new_name))
            {
                $errors[$key] = self::uploadError(self::UPLOAD_ERR_MOVE);
                continue;
            }
            Log::log(self::CLASSNAME, "Upload File: " . $new_name);
        }

        if ($errors)
            Log::log(self::CLASSNAME, "Upload File Errors : " . Text::arrayToString($errors));
        return empty($errors) ? $new_name : $errors;
    }

    public static function uploadError($code = null)
    {
        $arr = array(
            UPLOAD_ERR_OK => 'Файлът е качен успешно.',
            UPLOAD_ERR_INI_SIZE => 'Файлът превиши параметъра `upload_max_filesize` зададен в php.ini. ',
            UPLOAD_ERR_FORM_SIZE => 'Файлът превиши параметъра `MAX_FILE_SIZE` зададен в HTML формата. ',
            UPLOAD_ERR_PARTIAL => 'Файлът беше частично качен. ',
            UPLOAD_ERR_NO_FILE => 'Файлът не беше качен.',
            UPLOAD_ERR_NO_TMP_DIR => 'Липсва времената директория.',
            UPLOAD_ERR_CANT_WRITE => 'Файлът не може да бъде записан на диска.',
            UPLOAD_ERR_EXTENSION => 'PHP extension спря качването на файла.',
            self::UPLOAD_ERR_UNEXPECTED => 'Неочаквана грешка.',
            self::UPLOAD_ERR_MOVE => 'Файлът не може да бъде преместен.',
        );

        if (!($code !== null && isset($arr[$code])))
        {
            return $arr;
        }

        return $arr[$code];
    }

    public static function array2csv(array $data, array $cols = array())
    {
        $fp = fopen('php://output', 'w');
        ob_start();
        if ($cols)
        {
            fputcsv($fp, $cols);
        }
        foreach ($data as $row)
        {
            fputcsv($fp, $row);
        }
        fclose($fp);
        return ob_get_clean();
    }

    public static function download($src, $dest, $chunk = 8192)
    {
        $file = fopen($src, "rb");
        if (!$file)
        {
            return false;
        }

        $new = fopen($dest, "wb");
        if (!$new)
        {
            return false;
        }

        while (!feof($file))
        {
            fwrite($new, fread($file, $chunk), $chunk);
        }

        fclose($file);
        fclose($new);
    }

    public static function CURLGetFile($url, $options = array())
    {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
        if (isset($optins['userAgent']))
            curl_setopt($c, CURLOPT_HTTPHEADER, array('User-Agent: $userAgent'));
        $content = curl_exec($c);
        curl_close($c);

        return $content;
    }

}
