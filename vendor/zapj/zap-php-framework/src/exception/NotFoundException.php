<?php

namespace zap\exception;

class NotFoundException extends HttpException
{
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(404, $message ?: '请求的资源未找到', $code, $previous);
    }
}