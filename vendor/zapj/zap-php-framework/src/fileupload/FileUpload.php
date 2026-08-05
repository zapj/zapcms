<?php

namespace zap\fileupload;

/**
 * 文件上传组件
 *
 * 提供文件上传处理、验证和保存功能，支持单文件和多文件上传。
 *
 * 基本用法：
 *
 * ```php
 * use zap\fileupload\FileUpload;
 *
 * $uploader = new FileUpload();
 *
 * // 设置限制
 * $uploader->setAllowedTypes(['jpg', 'png', 'gif', 'pdf']);
 * $uploader->setMaxSize(5 * 1024 * 1024); // 5MB
 *
 * // 单文件上传
 * $file = $uploader->upload('avatar', '/path/to/uploads');
 * echo $file->getSavedPath();
 *
 * // 自定义文件名
 * $file = $uploader->upload('avatar', '/path/to/uploads', 'user_123_avatar');
 *
 * // 多文件上传
 * $files = $uploader->uploadMultiple('photos', '/path/to/uploads');
 * foreach ($files as $file) {
 *     echo $file->getSavedPath();
 * }
 * ```
 */
class FileUpload
{
    /** @var array 允许的文件扩展名（小写） */
    protected array $allowedTypes = [];

    /** @var array 允许的 MIME 类型 */
    protected array $allowedMimes = [];

    /** @var int 最大文件大小（字节），0 = 不限制 */
    protected int $maxSize = 0;

    /** @var bool 是否自动重命名（避免覆盖） */
    protected bool $autoRename = true;

    /** @var string|null 文件名生成回调，签名: function(UploadedFile $file, string $suggestedName): string */
    protected $nameCallback = null;

    /** @var UploadedFile[] 最近一次上传的文件列表 */
    protected array $files = [];

    // ───────────────────── 配置方法 ─────────────────────

    /**
     * 设置允许的文件扩展名
     *
     * @param string[] $types 扩展名数组，如 ['jpg', 'png', 'gif']，不含点，大小写不敏感
     * @return $this
     */
    public function setAllowedTypes(array $types): self
    {
        $this->allowedTypes = array_map('strtolower', $types);
        return $this;
    }

    /**
     * 获取允许的文件扩展名
     *
     * @return string[]
     */
    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }

    /**
     * 设置允许的 MIME 类型
     *
     * @param string[] $mimes MIME 类型数组，如 ['image/jpeg', 'image/png']
     * @return $this
     */
    public function setAllowedMimes(array $mimes): self
    {
        $this->allowedMimes = $mimes;
        return $this;
    }

    /**
     * 获取允许的 MIME 类型
     *
     * @return string[]
     */
    public function getAllowedMimes(): array
    {
        return $this->allowedMimes;
    }

    /**
     * 设置最大文件大小
     *
     * @param int $bytes 最大字节数，0 表示不限制
     * @return $this
     */
    public function setMaxSize(int $bytes): self
    {
        $this->maxSize = $bytes;
        return $this;
    }

    /**
     * 获取最大文件大小
     *
     * @return int 字节数
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * 设置是否自动重命名文件（避免覆盖）
     *
     * 自动重命名时会在文件名后添加时间戳和随机字符串。
     *
     * @param bool $autoRename
     * @return $this
     */
    public function setAutoRename(bool $autoRename): self
    {
        $this->autoRename = $autoRename;
        return $this;
    }

    /**
     * 是否启用自动重命名
     */
    public function isAutoRename(): bool
    {
        return $this->autoRename;
    }

    /**
     * 设置文件名生成回调
     *
     * 回调签名: function(UploadedFile $file, string $suggestedName): string
     * 返回不含扩展名的文件名。
     *
     * @param callable $callback
     * @return $this
     */
    public function setNameCallback(callable $callback): self
    {
        $this->nameCallback = $callback;
        return $this;
    }

    // ───────────────────── 上传方法 ─────────────────────

    /**
     * 上传单个文件
     *
     * @param string      $key       表单字段名
     * @param string      $targetDir 目标目录
     * @param string|null $name      保存的文件名（不含扩展名），null 表示使用原文件名
     * @return UploadedFile
     * @throws FileUploadException
     */
    public function upload(string $key, string $targetDir, ?string $name = null): UploadedFile
    {
        if (!isset($_FILES[$key])) {
            throw new FileUploadException("上传字段 '{$key}' 不存在。", 0, $key);
        }

        $raw = $_FILES[$key];

        // 多文件数组当成单文件使用时报错
        if (is_array($raw['name'])) {
            throw new FileUploadException(
                "字段 '{$key}' 包含多个文件，请使用 uploadMultiple() 方法。",
                0,
                $key
            );
        }

        $file = new UploadedFile($raw);

        $this->validate($file, $key);

        $saveName = $this->resolveSaveName($file, $name);
        $targetPath = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $saveName . '.' . $file->getClientExtension();

        if (!$file->move($targetPath)) {
            throw new FileUploadException(
                "文件移动失败: {$file->getErrorMessage()}",
                $file->getError(),
                $key
            );
        }

        $this->files = [$file];
        return $file;
    }

    /**
     * 上传多个文件
     *
     * @param string      $key       表单字段名（需为数组类型 input name="files[]"）
     * @param string      $targetDir 目标目录
     * @param string|null $name      保存的基础文件名（不含扩展名），每个文件会自动添加序号
     * @return UploadedFile[]
     * @throws FileUploadException
     */
    public function uploadMultiple(string $key, string $targetDir, ?string $name = null): array
    {
        if (!isset($_FILES[$key])) {
            throw new FileUploadException("上传字段 '{$key}' 不存在。", 0, $key);
        }

        $raw = $_FILES[$key];

        // 单文件当成多文件用时的处理
        if (!is_array($raw['name'])) {
            $raw = [
                'name'     => [$raw['name']],
                'tmp_name' => [$raw['tmp_name']],
                'size'     => [$raw['size']],
                'type'     => [$raw['type']],
                'error'    => [$raw['error']],
            ];
        }

        $files = UploadedFile::normalize($raw);
        $savedFiles = [];
        $targetDir = rtrim($targetDir, '/\\');

        foreach ($files as $index => $file) {
            // 跳过没有实际文件的条目
            if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $this->validate($file, $key);

            $baseName = $name !== null ? "{$name}_{$index}" : null;
            $saveName = $this->resolveSaveName($file, $baseName);
            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $saveName . '.' . $file->getClientExtension();

            if (!$file->move($targetPath)) {
                throw new FileUploadException(
                    "文件 '{$file->getClientOriginalName()}' 移动失败: {$file->getErrorMessage()}",
                    $file->getError(),
                    $key
                );
            }

            $savedFiles[] = $file;
        }

        $this->files = $savedFiles;
        return $savedFiles;
    }

    // ───────────────────── 验证 ─────────────────────

    /**
     * 验证单个上传文件
     *
     * @throws FileUploadException
     */
    protected function validate(UploadedFile $file, string $field): void
    {
        // 检查上传是否成功
        if (!$file->isValid()) {
            throw new FileUploadException(
                "文件上传失败: {$file->getErrorMessage()}",
                $file->getError(),
                $field
            );
        }

        // 检查文件大小
        if ($this->maxSize > 0 && $file->getSize() > $this->maxSize) {
            $maxFormatted = $this->formatBytes($this->maxSize);
            $sizeFormatted = $this->formatBytes($file->getSize());
            throw new FileUploadException(
                "文件大小 {$sizeFormatted} 超过了限制 {$maxFormatted}。",
                0,
                $field
            );
        }

        // 检查扩展名
        if (!empty($this->allowedTypes)) {
            $ext = $file->getClientExtension();
            if (!in_array($ext, $this->allowedTypes, true)) {
                throw new FileUploadException(
                    "不允许的文件类型 '{$ext}'，允许的类型: " . implode(', ', $this->allowedTypes) . "。",
                    0,
                    $field
                );
            }
        }

        // 检查 MIME 类型
        if (!empty($this->allowedMimes)) {
            $mime = $file->getMimeType() ?: $file->getClientMimeType();
            if (!in_array($mime, $this->allowedMimes, true)) {
                throw new FileUploadException(
                    "不允许的 MIME 类型 '{$mime}'。",
                    0,
                    $field
                );
            }
        }
    }

    // ───────────────────── 辅助方法 ─────────────────────

    /**
     * 解析最终保存的文件名（不含扩展名）
     */
    protected function resolveSaveName(UploadedFile $file, ?string $suggestedName): string
    {
        // 自定义回调优先
        if ($this->nameCallback !== null) {
            return call_user_func($this->nameCallback, $file, $suggestedName ?? $file->getClientOriginalName());
        }

        $baseName = $suggestedName ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        if ($this->autoRename) {
            $baseName .= '_' . date('YmdHis') . '_' . substr(uniqid(), -6);
        }

        // 清理文件名中的危险字符
        $baseName = preg_replace('/[^a-zA-Z0-9_\-\x{4e00}-\x{9fff}\x{3000}-\x{303f}\x{ff00}-\x{ffef}]/u', '_', $baseName);

        return $baseName ?: 'file_' . time();
    }

    /**
     * 格式化字节数为可读格式
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    // ───────────────────── 结果获取 ─────────────────────

    /**
     * 获取最近一次上传的所有文件
     *
     * @return UploadedFile[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * 获取最近一次上传的第一个文件（单文件上传时使用）
     */
    public function getFile(): ?UploadedFile
    {
        return $this->files[0] ?? null;
    }
}
