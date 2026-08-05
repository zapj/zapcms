<?php

namespace zap\fileupload;

/**
 * 文件上传异常
 */
class FileUploadException extends \RuntimeException
{
    /** @var string|null 相关字段名 */
    protected ?string $field;

    public function __construct(string $message = '', int $code = 0, ?string $field = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->field = $field;
    }

    /**
     * 获取相关字段名
     */
    public function getField(): ?string
    {
        return $this->field;
    }
}
