<?php
namespace Saft\Backend\LocalStore\Test;

final class TestUtil
{
    private function TestUtil()
    {
    }

    public static function createTempDirectory()
    {
        // Use upload_tmp_dir instead of sys_get_temp_dir().
        // Example upload_tmp_dir: D:\xampp\tmp
        // Example sys_get_temp_dir(): C:\Windows\Temp
        $tempRoot = ini_get('upload_tmp_dir');
        $tempDirectory = tempnam($tempRoot,'');
        if (file_exists($tempDirectory)) {
            if (unlink($tempDirectory) === false) {
                throw new \Exception('Unable to delete ' . $tempDirectory);
            }
        }
        mkdir($tempDirectory);
        if (is_dir($tempDirectory)){
            return $tempDirectory;
        } else {
            throw new \Exception('Unable to create temporary directory. '
                . 'Temp Root: ' . $tempRoot);
        }
    }
    
    public static function deleteDirectory($dir)
    {
        if (is_null($dir)) {
            throw new \InvalidArgumentException('$dir is null');
        }

        $it = new \RecursiveDirectoryIterator($dir,
            \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it,
            \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                if (rmdir($file->getRealPath()) === false) {
                    throw new \Exception('Unable to delete directory ' . $file);
                }
            } else {
                if (unlink($file->getRealPath()) === false) {
                    throw new \Exception('Unable to delete file ' . $file);
                }
            }
        }
        rmdir($dir);
    }
}