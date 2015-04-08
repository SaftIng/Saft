<?php
namespace Saft\Backend\LocalStore\Test;

class TestUtil
{
    public static function createTempDirectory()
    {
        // Use upload_tmp_dir instead of sys_get_temp_dir().
        // Example upload_tmp_dir: D:\xampp\tmp
        // Example sys_get_temp_dir(): C:\Windows\Temp
        $tempRoot = ini_get('upload_tmp_dir');
        $tempDirectory = tempnam($tempRoot, '');
        if (file_exists($tempDirectory)) {
            if (@unlink($tempDirectory) === false) {
                throw new \Exception('Unable to delete ' . $tempDirectory);
            }
        }
        @mkdir($tempDirectory);
        if (is_dir($tempDirectory)) {
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

        $it = new \RecursiveDirectoryIterator(
            $dir,
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        $files = new \RecursiveIteratorIterator(
            $it,
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                if (@rmdir($file->getRealPath()) === false) {
                    throw new \Exception('Unable to delete directory ' . $file);
                }
            } else {
                if (@unlink($file->getRealPath()) === false) {
                    throw new \Exception('Unable to delete file ' . $file);
                }
            }
        }
        @rmdir($dir);
    }

    public static function copyDirectory($srcDir, $dstDir)
    {
        $it = new \RecursiveDirectoryIterator(
            $srcDir,
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        $files = new \RecursiveIteratorIterator(
            $it,
            \RecursiveIteratorIterator::SELF_FIRST
        );
        if (!is_dir($dstDir)) {
            if (@mkdir($dstDir) === false) {
                throw new \Exception('Unable to create directory ' . $dstDir);
            }
        }
        foreach ($files as $file) {
            if ($file->isDir()) {
                $success = mkdir($dstDir . DIRECTORY_SEPARATOR . $files->getSubPathName());
                if ($success === false) {
                    throw new \Exception('Unable to create directory ' . $files->getSubPathName());
                }
            } elseif ($file->isFile()) {
                $success = copy($file, $dstDir . DIRECTORY_SEPARATOR . $files->getSubPathName());
                if ($success === false) {
                    throw new \Exception('Unable to copy file ' . $files->getSubPathName());
                }
            }
        }
    }
}
