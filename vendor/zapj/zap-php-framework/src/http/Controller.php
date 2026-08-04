<?php

namespace zap\http;

abstract class Controller
{
    protected array $params = [];

    /**
     * 获取路由参数
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * 设置路由参数
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * 返回 JSON 响应
     *
     * @param mixed $data       响应数据
     * @param int   $statusCode HTTP 状态码
     * @return Response
     */
    protected function json($data = null, int $statusCode = 200): Response
    {
        $response = new Response($data, $statusCode);
        $response->withJson();
        return $response;
    }

    /**
     * 获取请求对象
     */
    protected function request(): ZapRequest
    {
        return ZapRequest::instance();
    }

    /**
     * 创建响应对象
     */
    protected function response($content = null, int $statusCode = 200): Response
    {
        return new Response($content, $statusCode);
    }

    /**
     * 将未定义的方法调用转发到 _invoke
     */
    public function __call(string $method, array $arguments)
    {
        if (method_exists($this, '_invoke')) {
            return $this->_invoke($method, $arguments);
        }
        throw new \BadMethodCallException("Method {$method} does not exist in " . static::class);
    }
}
