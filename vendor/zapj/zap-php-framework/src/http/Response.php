<?php

namespace zap\http;

class Response {

    private $statusCode = 200;
    private $headers = [];
    private $content;

    public function __construct($content = null, $statusCode = 200, $headers = []) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * 创建Response
     * @param null $content 响应内容
     * @param int $statusCode  HTTP Code
     * @param array|null $headers Headers
     * @return Response
     */
    public static function create($content = null, int $statusCode = 200, ?array $headers = []) {
        return new Response($content, $statusCode, $headers);
    }

    /**
     * 创建Json Response
     *
     * @param mixed $content 响应内容
     * @param int $statusCode  HTTP Code
     * @param array|null $headers Headers
     *
     * @return void
     */
    public static function json($content = null, int $statusCode = 200, ?array $headers = []) {


        (new Response($content, $statusCode, $headers))->withJson();
    }

    public function setStatusCode($code): Response
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * 设置Header
     * @param string $header Name
     * @param string $value  Value
     * @return $this
     */
    public function header(string $header, string $value): Response
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function withHeaders($headers): Response
    {
        foreach ($headers as $name=>$value){
            $this->headers[$name] = $value;
        }
        return $this;
    }

    public function withJson($data = null)
    {
        $data = $data ?? $this->content;
        $respond = json_encode($data,JSON_UNESCAPED_UNICODE);
        if ($respond === false) {
            $respond = json_encode(array("jsonError"=>json_last_error_msg(),"code"=>-1,"msg"=>"json parse error"));
        }
        $this->content = $respond;
        $this->header('Content-Type', 'application/json;charset=utf-8');
        $this->send();
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content): Response
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Response发送至浏览器
     */
    public function send() {
        if (\headers_sent()) {
            trigger_error('tried to change http response code after sending headers!',E_USER_ERROR);
        }
        http_response_code($this->statusCode);
        foreach ($this->headers as $header => $value) {
            header(strtoupper($header) . ': ' . $value);
        }
        echo $this->content;
        exit;
    }

    public function flash($message, $type = Session::INFO): Response
    {
        Session::instance()->add_flash($message,$type);
        return $this;
    }

    /**
     * Redirect
     * @param string $url
     * @param string|null $message
     * @param string $type Flash类型
     * @throws \Exception
     */
    public static function redirect(string $url, string $message = null, string $type = Session::INFO) {
        $response = Response::create()->header('Location',$url);
        if(!is_null($message)){
            $response->flash($message,$type);
        }
        $response->send();
    }



}