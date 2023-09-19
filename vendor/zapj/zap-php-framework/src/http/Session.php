<?php

namespace zap\http;

class Session
{

    public const INFO = 'info';
    public const SUCCESS = 'success';
    public const WARNING = 'warning';
    public const ERROR = 'error';
    const defaultType = 'info';

    /**
     *
     * @var Session
     */
    private static $instance;

    /**
     *
     * @return Session
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            if (!session_id() && !headers_sent()) {
                session_start();
            }
            static::$instance = new Session();
        }
        return static::$instance;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([static::instance(),$name],$arguments);
    }


    public function info($message)
    {
        return $this->add_flash($message, self::INFO);
    }

    public function success($message)
    {
        return $this->add_flash($message, self::SUCCESS);
    }


    public function warning($message)
    {
        return $this->add_flash($message, self::WARNING);
    }


    public function error($message)
    {
        return $this->add_flash($message, self::ERROR);
    }


    public function add_flash($message, $type = self::defaultType)
    {
        if (!isset($_SESSION['flash_messages']))
            $_SESSION['flash_messages'][$type] = array();
        $_SESSION['flash_messages'][$type][] = $message;
        return $this;
    }

    public function set($name, $value = null)
    {
        return $_SESSION[$name] = $value;
    }

    public function get($name, $default = null)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return $default;
    }

    public function has($name)
    {
        return empty($_SESSION[$name]) ? false : true;
    }

    public function remove($name)
    {
        if (is_array($name)) {
            foreach ($name as $key) {
                unset($_SESSION[$key]);
            }
        } else {
            unset($_SESSION[$name]);
        }
    }

    /**
     * 判断是否有错误
     * @return bool
     */
    public function hasErrors()
    {
        return empty($_SESSION['flash_messages'][self::ERROR]) ? false : true;
    }

    /**
     * 判断是否有此类型的Flash
     * @param null $type
     * @return bool
     */
    public function hasFlash($type = null)
    {
        if (!is_null($type)) {
            if (!empty($_SESSION['flash_messages'][$type]))
                return $_SESSION['flash_messages'][$type];
        } else {
            foreach ([self::ERROR, self::INFO, self::WARNING, self::SUCCESS] as $type) {
                if (isset($_SESSION['flash_messages'][$type]) && !empty($_SESSION['flash_messages'][$type]))
                    return $_SESSION['flash_messages'][$type];
            }
        }
        return false;
    }

    /**
     * Session getFlash
     * 根据类型获取Flash
     * @param null $type
     * @return array|mixed
     */
    public function getFlash($type = null)
    {
        $flash = [];
        if ($type == NULL && isset($_SESSION['flash_messages'])) {
            $flash = $_SESSION['flash_messages'];
            unset($_SESSION['flash_messages']);
        } else if (isset($_SESSION['flash_messages'][$type])) {
            $flash = $_SESSION['flash_messages'][$type];
            unset($_SESSION['flash_messages'][$type]);
        }
        return $flash;
    }


    /**
     * 清除Flash
     * @param array $types
     * @return $this
     */
    public function clearFlash($types = null)
    {
        if (is_null($types)) {
            unset($_SESSION['flash_messages']);
        } elseif (!is_array($types)) {
            $types = [$types];
        }
        foreach ($types as $type) {
            unset($_SESSION['flash_messages'][$type]);
        }
        return $this;
    }
}
