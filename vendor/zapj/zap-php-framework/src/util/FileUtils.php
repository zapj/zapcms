<?php

namespace zap\util;

/**
 * 文件系统工具
 */
class FileUtils
{
    // ========== 路径 ==========

    /**
     * 确保目录存在，不存在则创建
     */
    public static function ensureDir(string $path, int $mode = 0777): bool
    {
        if (is_dir($path)) {
            return true;
        }
        if (is_file($path)) {
            return false;
        }
        return @mkdir($path, $mode, true);
    }

    /**
     * 获取文件扩展名（不含点）
     */
    public static function extension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * 获取文件名（不含扩展名）
     */
    public static function basename(string $filename): string
    {
        return pathinfo($filename, PATHINFO_FILENAME);
    }

    /**
     * 获取 MIME 类型
     */
    public static function mimeType(string $filename)
    {
        if (!is_file($filename)) {
            return false;
        }
        if (function_exists('mime_content_type')) {
            return mime_content_type($filename);
        }
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $type;
        }
        return false;
    }

    // ========== 读写 ==========

    public static function copy(string $from, string $to): bool
    {
        self::ensureDir(dirname($to));
        return @copy($from, $to);
    }

    public static function write(string $filename, string $content, int $flags = 0): bool
    {
        self::ensureDir(dirname($filename));
        return @file_put_contents($filename, $content, $flags) !== false;
    }

    /**
     * 按行写入数组
     */
    public static function writeLines(string $filename, array $content = []): bool
    {
        self::ensureDir(dirname($filename));
        $fh = @fopen($filename, 'w');
        if (!$fh) {
            return false;
        }
        foreach ($content as $line) {
            fwrite($fh, $line . PHP_EOL);
        }
        return fclose($fh);
    }

    /**
     * 读取文件内容
     */
    public static function read(string $filename)
    {
        if (!is_file($filename)) {
            return false;
        }
        return file_get_contents($filename);
    }

    /**
     * 读取文件为数组（每行一个元素）
     */
    public static function readFileToArray(string $filename, ?int $flags = null)
    {
        if ($flags === null) {
            return file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }
        return file($filename, $flags);
    }

    // ========== 大小 ==========

    public static function sizeOf(string $filename, bool $fmt = false)
    {
        $size = @filesize($filename);
        if ($size === false) {
            $size = 0;
        }
        if ($fmt) {
            return Fmt::ByteToHuman($size);
        }
        return $size;
    }

    public static function sizeOfDir(string $path): int
    {
        $bytesTotal = 0;
        $path = realpath($path);
        if ($path === false || !is_dir($path)) {
            return 0;
        }
        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        ) as $object) {
            $bytesTotal += $object->getSize();
        }
        return $bytesTotal;
    }

    // ========== 删除 ==========

    public static function delete(string $filename): bool
    {
        if (!is_file($filename)) {
            return false;
        }
        return @unlink($filename);
    }

    /**
     * 递归删除目录
     */
    public static function deleteDir(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        // 安全检查：不允许删除根目录或用户目录
        $realPath = realpath($dir);
        if ($realPath === false || $realPath === DIRECTORY_SEPARATOR || dirname($realPath) === $realPath) {
            return false;
        }

        $iterator = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }
        return @rmdir($dir);
    }

    /**
     * 递归扫描目录下的所有文件
     *
     * @return array<int, string> 文件路径数组
     */
    public static function scan(string $dir, string $pattern = '*', bool $recursive = true): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        if ($recursive) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            );
            $files = [];
            foreach ($iterator as $file) {
                if ($pattern === '*' || fnmatch($pattern, $file->getFilename())) {
                    $files[] = $file->getRealPath();
                }
            }
            return $files;
        }

        return glob($dir . DIRECTORY_SEPARATOR . $pattern) ?: [];
    }
}
