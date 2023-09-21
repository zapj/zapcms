<?php

namespace zap\http;

class Session
{

    public const INFO = 'info';
    public const SUCCESS = 'success';
    public const WARNING = 'warning';
    public const ERROR = 'error';
    const defaultType = 'info';
    const FLASH_MESSAGE_KEY = '_flash_messages';

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


    public function info($message): Session
    {
        return $this->add_flash($message, self::INFO);
    }

    public function success($message): Session
    {
        return $this->add_flash($message, self::SUCCESS);
    }


    public function warning($message): Session
    {
        return $this->add_flash($message, self::WARNING);
    }


    public function error($message): Session
    {
        return $this->add_flash($message, self::ERROR);
    }


    public function add_flash($message, $type = self::defaultType): Session
    {
        if (!isset($_SESSION[self::FLASH_MESSAGE_KEY]))
            $_SESSION[self::FLASH_MESSAGE_KEY][$type] = array();
        $_SESSION[self::FLASH_MESSAGE_KEY][$type][] = $message;
        return $this;
    }

    public function set($name, $value = null)
    {
        $_SESSION[$name] = $value;
        return $this;
    }

    public function get($name, $default = null)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return $default;
    }

    public function has($name): bool
    {
        return !empty($_SESSION[$name]);
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
    public function hasErrors(): bool
    {
        return !empty($_SESSION[self::FLASH_MESSAGE_KEY][self::ERROR]);
    }

    /**
     * 判断是否有此类型的Flash
     * @param null $type
     * @return bool
     */
    public function hasFlash($type = null): bool
    {
        if (!is_null($type)) {
            if (!empty($_SESSION[self::FLASH_MESSAGE_KEY][$type]))
                return true;
        } else {
            foreach ([self::ERROR, self::INFO, self::WARNING, self::SUCCESS] as $type) {
                if (!empty($_SESSION[self::FLASH_MESSAGE_KEY][$type]))
                    return true;
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
        if ($type == NULL && isset($_SESSION[self::FLASH_MESSAGE_KEY])) {
            $flash = $_SESSION[self::FLASH_MESSAGE_KEY];
            unset($_SESSION[self::FLASH_MESSAGE_KEY]);
        } else if (isset($_SESSION[self::FLASH_MESSAGE_KEY][$type])) {
            $flash = $_SESSION[self::FLASH_MESSAGE_KEY][$type];
            unset($_SESSION[self::FLASH_MESSAGE_KEY][$type]);
        }
        return $flash;
    }


    /**
     * 清除Flash
     * @param array|null|string $types
     * @return $this
     */
    public function clearFlash($types = null): Session
    {
        if (is_null($types)) {
            unset($_SESSION[self::FLASH_MESSAGE_KEY]);
        } elseif (!is_array($types)) {
            $types = [$types];
        }
        foreach ($types as $type) {
            unset($_SESSION[self::FLASH_MESSAGE_KEY][$type]);
        }
        return $this;
    }
}
