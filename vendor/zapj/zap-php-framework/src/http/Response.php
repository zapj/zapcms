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
     * @param null $headers Headers
     * @return Response
     */
    public static function create($content = null, $statusCode = 200, $headers = []) {
        return new Response($content, $statusCode, $headers);
    }

    /**
     * 创建Json Response
     *
     * @param null|array|object $content 响应内容
     * @param int $statusCode  HTTP Code
     * @param null $headers Headers
     *
     * @return void
     */
    public static function json($content = null, $statusCode = 200, $headers = []) {
        $json = json_encode($content);
        if ($json === false) {
            $json = json_encode(array("jsonError"=>json_last_error_msg(),"code"=>-1,"msg"=>"json parse error"));
        }

        (new Response($json, $statusCode, $headers))->setHeader('Content-Type', 'application/json;charset=utf-8')->send();
    }

    public function setStatusCode($code) {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * 设置Header
     * @param string $header Name
     * @param string $value  Value
     * @return $this
     */
    public function setHeader($header, $value) {
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * 设置响应内容
     * @param string $content 响应内容
     */
    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    /**
     * Response发送至浏览器
     * @throws \Exception
     */
    public function send() {
        if (\headers_sent()) {
            throw new \Exception('tried to change http response code after sending headers!');
        }
        http_response_code($this->statusCode);
        foreach ($this->headers as $header => $value) {
            header(strtoupper($header) . ': ' . $value);
        }
        echo $this->content;
        die;
    }

    public function flash($message, $type = Session::INFO){
        Session::instance()->add_flash($message,$type);
        return $this;
    }

    /**
     * Redirect
     * @param string $url
     * @throws \Exception
     */
    public static function redirect($url,$message = null,$type = Session::INFO) {
        $response = Response::create()->setHeader('Location',$url);
        if(!is_null($message)){
            $response->flash($message,$type);
        }
        $response->send();
    }

}