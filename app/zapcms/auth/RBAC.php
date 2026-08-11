<?php

namespace zapcms\auth;

use zapcms\services\Auth;
use zap\DB;
use zap\facades\Cache;

class RBAC
{
    /** 缓存 TTL（秒） */
    protected int $cacheTtl = 3600;

    /** 版本号的 TTL（30 天，基本永不过期） */
    protected int $versionTtl = 2592000;

    protected ?int $userId = null;
    protected ?int $cacheVersion = null;
    protected ?array $roleIds = null;
    protected ?array $permissionKeys = null;
    protected ?array $rolePermissions = null;
    /** @var array|null [perm_key => []]  */
    protected ?array $userExtrasCache = null;

    // ================================================================
    //  缓存控制
    // ================================================================

    /**
     * 全局缓存失效 — 权限/角色变更时调用
     */
    public static function invalidate(): void
    {
        Cache::increment('rbac_version', 1);
    }

    /**
     * 单个用户缓存失效 — 用户角色分配变更时调用
     */
    public static function invalidateUser(int $userId): void
    {
        $version = Cache::get('rbac_version', 0) ?: 0;
        // 删除当前版本 + 前一个版本的 key（防止并发漏删）
        foreach ([$version, $version - 1] as $v) {
            if ($v >= 0) {
                Cache::delete("rbac:v{$v}:roles:{$userId}");
                Cache::delete("rbac:v{$v}:role_rows:{$userId}");
                Cache::delete("rbac:v{$v}:perms:{$userId}");
            }
        }
        Cache::delete("rbac:extras:{$userId}");
    }

    /**
     * 获取当前缓存版本号
     */
    protected function getVersion(): int
    {
        if ($this->cacheVersion === null) {
            $this->cacheVersion = Cache::get('rbac_version', 0, $this->versionTtl);
        }
        return $this->cacheVersion;
    }

    /**
     * 拼接带版本的缓存 key
     */
    protected function cacheKey(string $type): string
    {
        return "rbac:v{$this->getVersion()}:{$type}:{$this->getUserId()}";
    }

    // ================================================================
    //  用户上下文
    // ================================================================

    /**
     * 设置当前用户上下文，并重置所有内存缓存
     */
    public function setUser(?int $userId): self
    {
        $this->userId = $userId;
        $this->roleIds = null;
        $this->permissionKeys = null;
        $this->rolePermissions = null;
        $this->userExtrasCache = null;
        return $this;
    }

    /**
     * 获取当前用户 ID
     */
    public function getUserId(): int
    {
        if ($this->userId === null) {
            $this->userId = (int)Auth::user('id');
        }
        return $this->userId;
    }

    // ================================================================
    //  权限检查
    // ================================================================

    /**
     * 权限检查（支持子权限粒度）
     * @param string $permKey   权限标识
     * @param string|null $extraKey  子权限 key（如 view/add/edit），null 仅检查主权限
     *
     * 规则：
     *  - 超级管理员（role_id=1）拥有全部
     *  - 拥有主权限但权限本身没有定义 extras → 拥有全部
     *  - 拥有主权限且定义了 extras，但未指派任何子项 → 子权限全部拒绝
     *  - 拥有主权限且指派了部分子项 → 仅指派的通过
     */
    public function check($permKey, $extraKey = null): bool
    {
        if (empty($permKey)) {
            return false;
        }

        // 超级管理员拥有全部
        if ($this->isSuperAdmin()) {
            return true;
        }

        // 只检查主权限
        if ($extraKey === null) {
            $permissionKeys = $this->getPermissionKeys();
            return in_array($permKey, $permissionKeys, true);
        }

        // 检查子权限
        $extrasData = $this->getUserExtras($permKey);
        if ($extrasData === null) {
            return false;
        }

        $hasDefinedExtras = !empty($extrasData['__has_extras__']);
        unset($extrasData['__has_extras__']);

        if (!$hasDefinedExtras) {
            return true;
        }

        if (empty($extrasData)) {
            return false;
        }

        return isset($extrasData[$extraKey]);
    }

    // ================================================================
    //  查询方法
    // ================================================================

    public function roles(?int $userId = null): array
    {
        $userId = $userId ?? $this->getUserId();

        // 非当前用户不走缓存，直接查
        if ($userId !== $this->userId) {
            return DB::table('roles', 'r')
                ->join('admin_roles', 'ar', 'r.role_id=ar.role_id')
                ->where('ar.admin_id', $userId)
                ->fetchAll(FETCH_ASSOC);
        }

        $key = $this->cacheKey('role_rows');
        return Cache::get($key, function () use ($userId) {
            return DB::table('roles', 'r')
                ->join('admin_roles', 'ar', 'r.role_id=ar.role_id')
                ->where('ar.admin_id', $userId)
                ->fetchAll(FETCH_ASSOC);
        }, $this->cacheTtl);
    }

    public function getExtras(string $permKey, ?int $userId = null): ?array
    {
        $data = $this->getUserExtras($permKey, $userId);
        if ($data === null) {
            return null;
        }
        unset($data['__has_extras__']);
        return $data;
    }

    public function hasFullExtras(string $permKey, ?int $userId = null): bool
    {
        $data = $this->getUserExtras($permKey, $userId);
        if ($data === null || !empty($data['__has_extras__'])) {
            return false;
        }
        return true;
    }

    public function permissions(?int $userId = null): array
    {
        return $this->getPermissionKeys($userId);
    }

    public function hasRole($roleId, ?int $userId = null): bool
    {
        $userId = $userId ?? $this->getUserId();

        if (is_numeric($roleId)) {
            $roleIds = $this->getRoleIds($userId);
            return in_array((int)$roleId, $roleIds, true);
        }

        return DB::table('roles', 'r')
            ->join('admin_roles', 'ar', 'r.role_id=ar.role_id')
            ->where('ar.admin_id', $userId)
            ->where('r.name', $roleId)
            ->exists();
    }

    // ================================================================
    //  内部方法
    // ================================================================

    public function isSuperAdmin(?int $userId = null): bool
    {
        // 直接查 DB 避免缓存污染导致误判
        $uid = $userId ?? $this->getUserId();
        return DB::table('admin_roles')
            ->where('admin_id', $uid)
            ->where('role_id', 1)
            ->exists();
    }

    /**
     * 获取用户的所有角色 ID（带缓存）
     */
    protected function getRoleIds(?int $userId = null): array
    {
        $userId = $userId ?? $this->getUserId();

        // 非当前用户不走缓存
        if ($userId !== $this->userId) {
            $rows = DB::table('admin_roles')->where('admin_id', $userId)->fetchAll();
            return array_column($rows, 'role_id');
        }

        if ($this->roleIds !== null) {
            return $this->roleIds;
        }

        $key = $this->cacheKey('roles');

        // 先尝试从缓存读取，并验证格式（修复旧版 roles() 污染缓存的问题）
        $cached = Cache::get($key);
        if (is_array($cached) && !empty($cached)) {
            $first = reset($cached);
            // 如果缓存数据是关联数组（被旧版 roles() 污染），丢弃并重新查询
            if (is_array($first)) {
                Cache::delete($key);
                $cached = null;
            }
        }

        if (is_array($cached)) {
            $this->roleIds = $cached;
        } else {
            $this->roleIds = Cache::get($key, function () use ($userId) {
                $rows = DB::table('admin_roles')->where('admin_id', $userId)->fetchAll();
                return array_column($rows, 'role_id');
            }, $this->cacheTtl);
        }

        return $this->roleIds;
    }

    /**
     * 获取用户所有的 perm_key，超级管理员拥有全部权限（带缓存）
     */
    protected function getPermissionKeys(?int $userId = null): array
    {
        $userId = $userId ?? $this->getUserId();

        // 非当前用户不走缓存
        if ($userId !== $this->userId) {
            return $this->queryPermissionKeys($userId);
        }

        if ($this->permissionKeys !== null) {
            return $this->permissionKeys;
        }

        $key = $this->cacheKey('perms');
        $this->permissionKeys = Cache::get($key, fn() => $this->queryPermissionKeys($userId), $this->cacheTtl);
        return $this->permissionKeys;
    }

    /**
     * 从 DB 查询用户的 perm_key 列表
     */
    protected function queryPermissionKeys(int $userId): array
    {
        $roleIds = $this->getRoleIds($userId);
        if (empty($roleIds)) {
            return [];
        }

        if (in_array(1, $roleIds, true)) {
            $rows = DB::table('permissions')
                ->where('perm_key', '!=', '')
                ->select('perm_key')
                ->fetchAll();
            return array_column($rows, 'perm_key');
        }

        $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
        $rows = DB::query(
            "SELECT DISTINCT perm_key FROM {roles_permissions} WHERE role_id IN ({$placeholders})",
            $roleIds
        )->fetchAll();
        return array_column($rows, 'perm_key');
    }

    /**
     * 获取用户对某个权限的子权限映射（带缓存）
     */
    protected function getUserExtras(string $permKey, ?int $userId = null): ?array
    {
        $userId = $userId ?? $this->getUserId();

        // 超级管理员拥有全部
        if ($this->isSuperAdmin($userId)) {
            return ['__has_extras__' => false];
        }

        // 非当前用户不走缓存
        if ($userId !== $this->userId) {
            return $this->queryUserExtras($permKey, $userId);
        }

        if ($this->userExtrasCache !== null) {
            return $this->userExtrasCache[$permKey] ?? null;
        }

        // 缓存整个用户的 extras map
        $key = $this->cacheKey('extras');
        $this->userExtrasCache = Cache::get($key, fn() => $this->queryUserExtrasAll($userId), $this->cacheTtl);

        return $this->userExtrasCache[$permKey] ?? null;
    }

    /**
     * 从 DB 查询单个 permKey 的 extras
     */
    protected function queryUserExtras(string $permKey, int $userId): ?array
    {
        return $this->queryUserExtrasAll($userId)[$permKey] ?? null;
    }

    /**
     * 查询用户所有权限的 extras map（含内部标记 __has_extras__）
     */
    protected function queryUserExtrasAll(int $userId): array
    {
        $roleIds = $this->getRoleIds($userId);
        if (empty($roleIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
        $rows = DB::query(
            "SELECT perm_key, extras FROM {roles_permissions} WHERE role_id IN ({$placeholders})",
            $roleIds
        )->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $pk = $row['perm_key'];
            if (!isset($result[$pk])) {
                $result[$pk] = [];
            }
            $extraStr = $row['extras'] ?? '';
            if (!empty($extraStr)) {
                foreach (explode(',', $extraStr) as $part) {
                    $part = trim($part);
                    if ($part !== '') {
                        $result[$pk][$part] = true;
                    }
                }
            }
        }

        // 批量补 __has_extras__ 标记
        if (!empty($result)) {
            $pkList = array_keys($result);
            $pkp = implode(',', array_fill(0, count($pkList), '?'));
            $permRows = DB::query(
                "SELECT perm_key, extras FROM {permissions} WHERE perm_key IN ({$pkp})",
                $pkList
            )->fetchAll();

            $defined = [];
            foreach ($permRows as $pr) {
                $decoded = json_decode($pr['extras'] ?? '{}', true);
                $defined[$pr['perm_key']] = !empty($decoded);
            }
            foreach ($result as $pk => &$data) {
                $data['__has_extras__'] = $defined[$pk] ?? false;
            }
            unset($data);
        }

        return $result;
    }

    public function permissionsByRole(?int $userId = null): array
    {
        $userId = $userId ?? $this->getUserId();

        if ($this->rolePermissions !== null && $userId === $this->userId) {
            return $this->rolePermissions;
        }

        $roleIds = $this->getRoleIds($userId);
        $this->rolePermissions = [];
        foreach ($roleIds as $roleId) {
            $this->rolePermissions[$roleId] = RolesPermissions::getByRoleId($roleId);
        }
        return $this->rolePermissions;
    }
}
