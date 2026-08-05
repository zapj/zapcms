<?php

namespace zap\fileupload;

/**
 * 单个上传文件的封装
 *
 * @property-read string $name      客户端原始文件名
 * @property-read string $tmpName   临时文件路径
 * @property-read int    $size      文件大小（字节）
 * @property-read string $mimeType  文件 MIME 类型
 * @property-read int    $error     上传错误码
 * @property-read string $extension 文件扩展名（小写，不含点）
 */
class UploadedFile
{
    /** @var string 客户端原始文件名 */
    protected string $name;

    /** @var string 临时文件路径 */
    protected string $tmpName;

    /** @var int 文件大小（字节） */
    protected int $size;

    /** @var string MIME 类型 */
    protected string $mimeType;

    /** @var int 上传错误码 */
    protected int $error;

    /** @var string|null 移动后的最终路径 */
    protected ?string $savedPath = null;

    /**
     * 从 $_FILES 数组项创建实例
     */
    public function __construct(array $file)
    {
        $this->name     = $file['name']     ?? '';
        $this->tmpName  = $file['tmp_name'] ?? '';
        $this->size     = (int)($file['size'] ?? 0);
        $this->mimeType = $file['type']     ?? '';
        $this->error    = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    }

    /**
     * 获取客户端原始文件名
     */
    public function getClientOriginalName(): string
    {
        return $this->name;
    }

    /**
     * 获取文件扩展名（小写，不含点）
     */
    public function getClientExtension(): string
    {
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    /**
     * 获取文件大小（字节）
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * 获取 MIME 类型（由浏览器提供，不可完全信任）
     */
    public function getClientMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * 获取服务器端检测的真实 MIME 类型
     */
    public function getMimeType(): string
    {
        if (!is_file($this->tmpName)) {
            return '';
        }
        if (function_exists('mime_content_type')) {
            return mime_content_type($this->tmpName) ?: '';
        }
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type  = finfo_file($finfo, $this->tmpName);
            finfo_close($finfo);
            return $type ?: '';
        }
        return '';
    }

    /**
     * 获取临时文件路径
     */
    public function getTmpName(): string
    {
        return $this->tmpName;
    }

    /**
     * 获取上传错误码
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * 上传是否成功（无错误）
     */
    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK && is_uploaded_file($this->tmpName);
    }

    /**
     * 获取移动后的保存路径
     */
    public function getSavedPath(): ?string
    {
        return $this->savedPath;
    }

    /**
     * 将上传的文件移动到目标路径
     *
     * @param string $targetPath 目标绝对路径（含文件名）
     * @return bool
     */
    public function move(string $targetPath): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($this->tmpName, $targetPath)) {
            $this->savedPath = $targetPath;
            return true;
        }

        return false;
    }

    /**
     * 获取可读的错误消息
     */
    public function getErrorMessage(): string
    {
        return match ($this->error) {
            UPLOAD_ERR_OK         => '上传成功。',
            UPLOAD_ERR_INI_SIZE   => '文件大小超过了 php.ini 中 upload_max_filesize 的限制。',
            UPLOAD_ERR_FORM_SIZE  => '文件大小超过了表单 MAX_FILE_SIZE 的限制。',
            UPLOAD_ERR_PARTIAL    => '文件仅被部分上传。',
            UPLOAD_ERR_NO_FILE    => '没有文件被上传。',
            UPLOAD_ERR_NO_TMP_DIR => '服务器缺少临时文件夹。',
            UPLOAD_ERR_CANT_WRITE => '文件写入磁盘失败。',
            UPLOAD_ERR_EXTENSION  => '文件上传被 PHP 扩展停止。',
            default               => '未知上传错误。',
        };
    }

    /**
     * 将上传数据标准化为 UploadedFile 对象数组
     *
     * PHP 的 $_FILES 在处理数组形式的文件上传时结构比较特殊，
     * 此方法将其统一转换为 UploadedFile[] 格式。
     *
     * @param array $fileData $_FILES 中某个 key 的值
     * @return static[]
     */
    public static function normalize(array $fileData): array
    {
        if (!isset($fileData['name'])) {
            return [];
        }

        // 单文件上传：name 是字符串
        if (is_string($fileData['name'])) {
            return [new static($fileData)];
        }

        // 多文件上传：name 是数组，需要转置
        $files = [];
        foreach ($fileData['name'] as $index => $name) {
            $files[] = new static([
                'name'     => $name,
                'tmp_name' => $fileData['tmp_name'][$index] ?? '',
                'size'     => $fileData['size'][$index]     ?? 0,
                'type'     => $fileData['type'][$index]     ?? '',
                'error'    => $fileData['error'][$index]    ?? UPLOAD_ERR_NO_FILE,
            ]);
        }

        return $files;
    }

    // ───────────────────── 魔术方法 ─────────────────────

    public function __get(string $name)
    {
        return match ($name) {
            'name'      => $this->name,
            'tmpName'   => $this->tmpName,
            'size'      => $this->size,
            'mimeType'  => $this->mimeType,
            'error'     => $this->error,
            'extension' => $this->getClientExtension(),
            default     => null,
        };
    }
}
