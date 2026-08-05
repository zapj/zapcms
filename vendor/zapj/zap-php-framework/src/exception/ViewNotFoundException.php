<?php

namespace zap\exception;

class ViewNotFoundException extends \RuntimeException
{
    protected string $viewName;

    /**
     * @param string          $viewName  视图名称
     * @param string          $message   错误消息
     * @param int             $code      错误码
     * @param \Throwable|null $previous  前一个异常
     */
    public function __construct(string $viewName = '', string $message = '', int $code = 0, \Throwable $previous = null)
    {
        $this->viewName = $viewName;
        if (empty($message)) {
            $message = "视图未找到: {$viewName}";
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * 获取缺失的视图名称
     */
    public function getViewName(): string
    {
        return $this->viewName;
    }
}