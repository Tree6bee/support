<?php

namespace Tree6bee\Support\Helpers;

use Exception;

/**
 * 文件io
 */
class Io
{
    /**
     * 读取文件内容
     */
    public static function read($file)
    {
        if (is_file($file) && is_readable($file)) {
            return file_get_contents($file);
        }
        throw new Exception($file . '文件不存在或则不可读');
    }

    /**
     * 文件写操作
     */
    public static function write($file, $content)
    {
        if (empty($file)) {
            throw new Exception('file参数不能为空');
        }

        if (is_file($file)) {
            if (is_writeable($file)) {
                file_put_contents($file, $content, FILE_APPEND);
                return true;
            }
            throw new Exception($file . '文件不可写');
        } else {
            $dir = dirname($file);
            if (! file_exists($dir) && ! @mkdir($dir, 0755, true)) {
                throw new Exception($dir . ' 目录生成失败');
            }
            file_put_contents($file, $content, FILE_APPEND);
            return true;
        }
    }

    /**
     * 删除文件
     */
    public static function delFile($file)
    {
        if (is_file($file) && ! unlink($file)) {
            throw new Exception('删除失败');
        }
        return true;
    }

    /**
     * 获取文件后缀
     */
    public static function getExt($file)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * 检测目录是否为空文件夹
     */
    public static function isEmptyDir($dir)
    {
        //包含 '.' 或 '..'
        return (($files = @scandir($dir)) && count($files) <= 2);
    }

    /**
     * 获取目录大小
     * 单位：字节 B
     */
    public static function getDirSize()
    {
        $dirsize = 0;
        $handle = @opendir($dir);
        if (! $handle) {
            throw new Exception($curDir . '目录不可读');
        }
        while (false !== ($FolderOrFile = readdir($handle))) {
            if ($FolderOrFile != "." && $FolderOrFile != "..") {
                $fullpath = $dir . DIRECTORY_SEPARATOR . $FolderOrFile;
                if (is_dir($fullpath)) {
                    $dirsize += self::getDirSize($fullpath);
                } else {
                    $dirsize += filesize($fullpath);
                }
            }
        }
        closedir($handle);
        return $dirsize;
    }

    /**
     * 获取指定目录下所有文件(不包含子目录)
     */
    public static function getAllFile($dir)
    {
        if (! is_dir($dir)) {
            throw new Exception('不是一个目录');
        }
        return scandir($dir);
    }

    /**
     * 清空目录下所有文件
     * @param  $dir 目录名称
     */
    public static function clearDir($dir, $delDir = false)
    {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);
        $curDir = @opendir($path);
        if (! $curDir) {
            throw new Exception($curDir . '目录不可读');
        }
        while ($fileName = readdir($curDir)) {
            if ($fileName != '.' || $fileName != '..') {
                $fullpath = $curDir . DIRECTORY_SEPARATOR . $fileName;
                if (is_dir($fullpath)) {
                    self::clearDir($fullpath, true);
                } else {
                    self::delFile($fullpath);
                }
            }
        }
        closedir($curDir);
        if ($delDir && ! @rmdir($dir)) {
            throw new Exception($dir . '目录删除失败');
        }
        return true;
    }
}
