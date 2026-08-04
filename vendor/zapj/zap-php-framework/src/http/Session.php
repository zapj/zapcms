<?php

namespace zap\http;

class Session
{
    const FLASH_MESSAGE_KEY = '__zap_flash__';
    const CSRF_TOKEN_KEY    = '__zap_csrf__';

    /** @var self 单例 */
    protected static ?Session $instance = null;

    /** @var bool 是否已启动 */
    protected bool $started = false;

    /** @var array 会话配置选项 */
    protected array $config = [];

    /** @var string 上次活动时间记录键 */
    protected string $lastActivityKey = '__zap_last_activity__';

    public function __construct()
    {
    }

    // ───────────────────── 静态代理 ─────────────────────

    /**
     * 静态方法代理，支持 Session::get() / Session::set() 等快捷调用
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        return static::getInstance()->$method(...$arguments);
    }

    /**
     * 获取单例
     */
    public static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    /**
     * 获取单例（instance 别名，向后兼容）
     */
    public static function instance(): self
    {
        return static::getInstance();
    }

    /**
     * 重置单例（主要用于测试）
     */
    public static function resetInstance(): void
    {
        static::$instance = null;
    }

    // ───────────────────── 配置与启动 ─────────────────────

    /**
     * 配置 Session 选项（在 start 之前调用）
     *
     * @param array $options 配置项，支持：
     *   - name            string  会话名称
     *   - save_path       string  自定义保存路径
     *   - cookie_lifetime int      Cookie 生命周期（秒），0=浏览器关闭时过期
     *   - cookie_path     string   Cookie 路径，默认 '/'
     *   - cookie_domain   string   Cookie 域名，默认 ''
     *   - cookie_secure   bool     仅 HTTPS 传输 Cookie，默认 false
     *   - cookie_httponly bool     HttpOnly 标志，默认 true
     *   - cookie_samesite string   SameSite: 'Lax' / 'Strict' / 'None'
     *   - gc_maxlifetime  int      服务端 GC 有效期（秒），默认 1440
     *   - gc_probability  int      GC 触发概率分子，默认 1
     *   - gc_divisor      int      GC 触发概率分母，默认 100
     *   - lazy_write      bool     仅在数据变化时写入，默认 true
     *   - strict_mode     bool     严格模式（拒绝未初始化 ID），默认 true
     *   - last_activity   bool     是否记录最后活动时间，默认 true
     * @return $this
     */
    public function configure(array $options = []): self
    {
        $this->config = array_merge([
            'name'            => 'ZAP_SESSION',
            'cookie_lifetime' => 0,
            'cookie_path'     => '/',
            'cookie_domain'   => '',
            'cookie_secure'   => false,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'gc_maxlifetime'  => 1440,
            'gc_probability'  => 1,
            'gc_divisor'      => 100,
            'lazy_write'      => true,
            'strict_mode'     => true,
            'last_activity'   => true,
        ], $options);

        return $this;
    }

    /**
     * 获取当前 Session 配置
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 启动 Session
     *
     * @param array $options 可选的运行时配置（会合并到默认配置）
     * @return $this
     */
    public function start(array $options = []): self
    {
        if ($this->started && session_status() === PHP_SESSION_ACTIVE) {
            return $this;
        }

        if ($options) {
            $this->configure($options);
        }

        // 确保配置已初始化
        if (empty($this->config)) {
            $this->configure();
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_name($this->config['name']);

            // 设置 Cookie 参数
            session_set_cookie_params([
                'lifetime' => $this->config['cookie_lifetime'],
                'path'     => $this->config['cookie_path'],
                'domain'   => $this->config['cookie_domain'],
                'secure'   => $this->config['cookie_secure'],
                'httponly' => $this->config['cookie_httponly'],
                'samesite' => $this->config['cookie_samesite'],
            ]);

            // 设置 Session 运行时参数
            if ($this->config['save_path'] ?? null) {
                session_save_path($this->config['save_path']);
            }

            ini_set('session.gc_maxlifetime', $this->config['gc_maxlifetime']);
            ini_set('session.gc_probability', $this->config['gc_probability']);
            ini_set('session.gc_divisor', $this->config['gc_divisor']);
            ini_set('session.lazy_write', $this->config['lazy_write'] ? '1' : '0');
            ini_set('session.use_strict_mode', $this->config['strict_mode'] ? '1' : '0');

            // 仅允许通过 HTTP 访问 Cookie，禁用 JS document.cookie 访问
            ini_set('session.cookie_httponly', $this->config['cookie_httponly'] ? '1' : '0');
            ini_set('session.cookie_secure', $this->config['cookie_secure'] ? '1' : '0');
            ini_set('session.cookie_samesite', $this->config['cookie_samesite']);

            session_start();
        }

        $this->started = true;

        // 记录最后活动时间
        if ($this->config['last_activity'] && !isset($_SESSION[$this->lastActivityKey])) {
            $_SESSION[$this->lastActivityKey] = time();
        }

        // 初始化 CSRF Token
        if (!isset($_SESSION[self::CSRF_TOKEN_KEY])) {
            $this->regenerateToken(true);
        }

        return $this;
    }

    /**
     * 检查会话是否已启动
     */
    public function isStarted(): bool
    {
        return $this->started && session_status() === PHP_SESSION_ACTIVE;
    }

    // ───────────────────── 读写 ─────────────────────

    /**
     * 读取值
     *
     * @param string $key     键名（点分路径支持 'user.name'）
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $this->start();
        return $this->dotGet($_SESSION, $key, $default);
    }

    /**
     * 写入值
     *
     * @return $this
     */
    public function set(string $key, $value): self
    {
        $this->start();
        $this->dotSet($_SESSION, $key, $value);

        // 更新最后活动时间
        if ($this->config['last_activity'] ?? true) {
            $_SESSION[$this->lastActivityKey] = time();
        }

        return $this;
    }

    /**
     * 检查键是否存在（不依赖值真假）
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        $this->start();
        return $this->dotHas($_SESSION, $key);
    }

    /**
     * 删除一个或多个键
     *
     * @param string|array $keys
     * @return $this
     */
    public function forget($keys): self
    {
        $this->start();
        foreach ((array)$keys as $key) {
            $this->dotDelete($_SESSION, $key);
        }
        return $this;
    }

    /**
     * 删除键（remove 别名，向后兼容）
     *
     * @param string $key
     * @return $this
     */
    public function remove(string $key): self
    {
        return $this->forget($key);
    }

    /**
     * 读取并删除
     *
     * @return mixed
     */
    public function pull(string $key, $default = null)
    {
        $value = $this->get($key, $default);
        $this->forget($key);
        return $value;
    }

    /**
     * 向数组推送元素
     *
     * @return $this
     */
    public function push(string $key, $value): self
    {
        $this->start();
        $arr = $this->get($key, []);
        if (!is_array($arr)) {
            $arr = [];
        }
        $arr[] = $value;
        $this->set($key, $arr);
        return $this;
    }

    /**
     * 递增
     *
     * @return $this
     */
    public function increment(string $key, int $amount = 1): self
    {
        $this->start();
        $value = (int)$this->get($key, 0) + $amount;
        $this->set($key, $value);
        return $this;
    }

    /**
     * 递减
     *
     * @return $this
     */
    public function decrement(string $key, int $amount = 1): self
    {
        return $this->increment($key, -$amount);
    }

    /**
     * 获取所有 Session 数据
     */
    public function all(): array
    {
        $this->start();
        return $_SESSION;
    }

    /**
     * 仅获取指定键（键不存在时跳过）
     */
    public function only(array $keys): array
    {
        $this->start();
        $result = [];
        foreach ($keys as $key) {
            // 仅当键存在时才返回，避免空值污染
            if ($this->dotHas($_SESSION, $key)) {
                // 重新读取避免 dotHas 引用传递副作用
                $result[$key] = $this->get($key);
            }
        }
        return $result;
    }

    /**
     * 排除指定键，返回其余所有数据
     */
    public function except(array $keys): array
    {
        $data = $this->all();
        foreach ($keys as $key) {
            unset($data[$key]);
        }
        return $data;
    }

    /**
     * Session 数据条数（不含系统键）
     */
    public function count(): int
    {
        $data = $this->all();
        unset($data[self::FLASH_MESSAGE_KEY], $data[self::CSRF_TOKEN_KEY], $data[$this->lastActivityKey]);
        return count($data);
    }

    /**
     * Session 是否为空（不含系统键）
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    // ───────────────────── Session ID ─────────────────────

    /**
     * 获取当前 Session ID
     */
    public function getId(): string
    {
        $this->start();
        return session_id();
    }

    /**
     * 设置 Session ID（在 start 之前调用）
     *
     * @return $this
     */
    public function setId(string $id): self
    {
        if (!$this->started && session_status() === PHP_SESSION_NONE) {
            session_id($id);
        }
        return $this;
    }

    /**
     * 重新生成 Session ID（防会话固定攻击）
     *
     * @param bool $deleteOld 是否删除旧 Session 文件
     * @return $this
     */
    public function regenerate(bool $deleteOld = false): self
    {
        $this->start();
        session_regenerate_id($deleteOld);
        return $this;
    }

    /**
     * 获取最后活动时间（Unix 时间戳）
     *
     * @return int|null 未启动或未记录时返回 null
     */
    public function lastActivity(): ?int
    {
        if (!$this->isStarted()) {
            return null;
        }
        return $_SESSION[$this->lastActivityKey] ?? null;
    }

    /**
     * 获取会话存活时长（秒）
     *
     * @return int|null
     */
    public function age(): ?int
    {
        $last = $this->lastActivity();
        if ($last === null) {
            return null;
        }
        return time() - $last;
    }

    // ───────────────────── 表单旧值 ─────────────────────

    /**
     * 保存当前请求数据到 Flash（供下一个请求读取旧值）
     *
     * @param array|null $data 要保存的数据，null 时自动读取 $_POST
     * @return $this
     */
    public function flashInput(?array $data = null): self
    {
        $this->start();
        $_SESSION[self::FLASH_MESSAGE_KEY]['__old__'] = $data ?? $_POST;
        return $this;
    }

    /**
     * 读取上一次请求的旧输入值
     *
     * @param string|null $key     字段名，null=返回全部旧值
     * @param mixed       $default 默认值
     * @return mixed
     */
    public function old(?string $key = null, $default = null)
    {
        $this->start();
        $old = $_SESSION[self::FLASH_MESSAGE_KEY]['__old__'] ?? [];

        if ($key === null) {
            return $old;
        }

        return $old[$key] ?? $default;
    }

    // ───────────────────── CSRF Token ─────────────────────

    /**
     * 获取当前 CSRF Token
     *
     * @return string
     */
    public function token(): string
    {
        $this->start();
        return $_SESSION[self::CSRF_TOKEN_KEY];
    }

    /**
     * 重新生成 CSRF Token
     *
     * @param bool $force 是否强制再生（不强制时仅当不存在时生成）
     * @return string
     */
    public function regenerateToken(bool $force = false): string
    {
        $this->start();
        if ($force || empty($_SESSION[self::CSRF_TOKEN_KEY])) {
            $_SESSION[self::CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::CSRF_TOKEN_KEY];
    }

    /**
     * 验证 CSRF Token
     *
     * @param string $token 待验证的 Token
     * @return bool
     */
    public function validateToken(string $token): bool
    {
        $this->start();
        if (empty($_SESSION[self::CSRF_TOKEN_KEY])) {
            return false;
        }
        return hash_equals($_SESSION[self::CSRF_TOKEN_KEY], $token);
    }

    // ───────────────────── Flash 消息 ─────────────────────

    /**
     * 写入 Flash 消息
     *
     * @param string $type    消息类型（如 success / error / warning / info）
     * @param string $message 消息内容
     * @return $this
     */
    public function flash(string $type, string $message): self
    {
        $this->start();
        $_SESSION[self::FLASH_MESSAGE_KEY][$type][] = [
            'message'   => $message,
            'timestamp' => time(),
        ];
        return $this;
    }

    /**
     * 写入 Flash 消息（add_flash 别名，参数顺序为 message, type，向后兼容）
     *
     * @param string $message 消息内容
     * @param string $type    消息类型，默认 'info'
     * @return $this
     */
    public function add_flash(string $message, string $type = 'info'): self
    {
        return $this->flash($type, $message);
    }

    /**
     * 读取并清除 Flash 消息（原格式，含 timestamp）
     *
     * @param string|array|null $types 消息类型，null=所有类型
     * @return array ['success' => [['message'=>'...', 'timestamp'=>...]]]
     */
    public function getFlash($types = null): array
    {
        $this->start();
        $flashMessages = [];

        if (empty($_SESSION[self::FLASH_MESSAGE_KEY])) {
            return $flashMessages;
        }

        // 过滤排除系统键
        $flashData = $_SESSION[self::FLASH_MESSAGE_KEY];
        unset($flashData['__old__']);

        if (is_null($types)) {
            // 只取闪存键，不包含 __old__
            $flashMessages = $flashData;
            unset($_SESSION[self::FLASH_MESSAGE_KEY]);
            // 恢复 __old__（避免丢失未读取的旧输入值）
            if (isset($flashData['__old__'])) {
                // 不做特殊处理，__old__ 在 getFlash 时通常由 old() 消费后清除
            }
            return $flashMessages;
        }

        if (!is_array($types)) {
            $types = [$types];
        }

        foreach ($types as $type) {
            if (isset($_SESSION[self::FLASH_MESSAGE_KEY][$type])) {
                $flashMessages[$type] = $_SESSION[self::FLASH_MESSAGE_KEY][$type];
                unset($_SESSION[self::FLASH_MESSAGE_KEY][$type]);
            }
        }

        if (empty($_SESSION[self::FLASH_MESSAGE_KEY])) {
            unset($_SESSION[self::FLASH_MESSAGE_KEY]);
        }

        return $flashMessages;
    }

    /**
     * 获取纯文本 Flash 消息（仅取 message 字符串，不含 timestamp）
     *
     * @param string|array|null $types 消息类型，null=所有
     * @return array ['success' => ['操作成功！'], 'error' => ['输入有误']]
     */
    public function getFlashMessages($types = null): array
    {
        $flash = $this->getFlash($types);
        $messages = [];
        foreach ($flash as $type => $items) {
            $messages[$type] = array_column($items, 'message');
        }
        return $messages;
    }

    /**
     * 检查是否有 Flash 消息
     *
     * @param string|null $type 指定类型，null=检查是否有任何消息
     * @return bool
     */
    public function hasFlash(?string $type = null): bool
    {
        $this->start();
        if (empty($_SESSION[self::FLASH_MESSAGE_KEY])) {
            return false;
        }
        if ($type === null) {
            // 存在非 __old__ 键即为有消息
            $keys = array_keys($_SESSION[self::FLASH_MESSAGE_KEY]);
            return array_filter($keys, fn($k) => $k !== '__old__') !== [];
        }
        return !empty($_SESSION[self::FLASH_MESSAGE_KEY][$type]);
    }

    /**
     * 清除 Flash 消息
     *
     * @param string|array|null $types 消息类型
     * @return $this
     */
    public function clearFlash($types = null): self
    {
        $this->start();

        if (is_null($types)) {
            unset($_SESSION[self::FLASH_MESSAGE_KEY]);
            return $this;
        }

        if (!is_array($types)) {
            $types = [$types];
        }

        foreach ($types as $type) {
            unset($_SESSION[self::FLASH_MESSAGE_KEY][$type]);
        }

        if (empty($_SESSION[self::FLASH_MESSAGE_KEY])) {
            unset($_SESSION[self::FLASH_MESSAGE_KEY]);
        }

        return $this;
    }

    // ───────────────────── 销毁 ─────────────────────

    /**
     * 销毁 Session
     *
     * @return void
     */
    public function destroy(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
        $this->started = false;
    }

    // ───────────────────── 点分路径辅助方法 ─────────────────────

    /**
     * @internal
     */
    private function dotGet(array &$array, string $key, $default = null)
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }
            $current = &$current[$segment];
        }
        return $current;
    }

    /**
     * @internal
     */
    private function dotSet(array &$array, string $key, $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $current[$segment] = $value;
                return;
            }
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }
    }

    /**
     * @internal
     */
    private function dotHas(array &$array, string $key): bool
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }
            $current = &$current[$segment];
        }
        return true;
    }

    /**
     * @internal
     */
    private function dotDelete(array &$array, string $key): void
    {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $current = &$array;

        foreach ($keys as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return;
            }
            $current = &$current[$segment];
        }
        unset($current[$lastKey]);
    }
}
