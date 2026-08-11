<?php

namespace zapcms\auth;

use zapcms\services\Auth;
use zap\DB;

class RBAC
{
    protected ?int $userId = null;
    protected ?array $roleIds = null;
    protected ?array $permissionKeys = null;
    protected ?array $rolePermissions = null;
    /** @var array|null [perm_key => []] 或 [perm_key => ['view'=>true, ...]] */
    protected ?array $userExtrasCache = null;

    /**
     * 设置当前用户上下文
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
     * @param string $permKey  权限标识
     * @param string|null $extraKey  子权限 key（如 view/add/edit），null 仅检查主权限
     *
     * 规则：
     *  - 超级管理员（role_id=1）拥有全部
     *  - 拥有主权限但权限本身没有定义 extras  → 拥有全部（无需关心子项）
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

        // 检查子权限：先看用户有没有这个权限
        $extrasData = $this->getUserExtras($permKey);
        if ($extrasData === null) {
            return false;            // 没有该权限
        }

        // 移除内部标记
        $hasDefinedExtras = !empty($extrasData['__has_extras__']);
        unset($extrasData['__has_extras__']);

        if (!$hasDefinedExtras) {
            // 权限本身没有定义 extras → 拥有主权限即拥有全部
            return true;
        }

        if (empty($extrasData)) {
            // 权限定义了 extras 但一个都没指派 → 拒绝所有子权限
            return false;
        }

        return isset($extrasData[$extraKey]);
    }

    // ================================================================
    //  查询方法
    // ================================================================

    /**
     * 获取当前用户拥有的所有角色
     */
    public function roles(?int $userId = null): array
    {
        $userId = $userId ?? $this->getUserId();
        return DB::table('roles', 'r')
            ->join('admin_roles', 'ar', 'r.role_id=ar.role_id')
            ->where('ar.admin_id', $userId)
            ->fetchAll(FETCH_ASSOC);
    }

    /**
     * 获取当前用户对某个权限的子权限集合
     * @return array|null
     *   null          = 没有该权限
     *   ['view'=>true,...] = 拥有的子权限
     *   （调用方通过 getExtrasCount 判断是否有全部）
     */
    public function getExtras(string $permKey, ?int $userId = null): ?array
    {
        $data = $this->getUserExtras($permKey, $userId);
        if ($data === null) {
            return null;
        }
        unset($data['__has_extras__']);
        return $data;
    }

    /**
     * 该权限是否拥有全部子权限（含本身就没有定义 extras 的权限）
     */
    public function hasFullExtras(string $permKey, ?int $userId = null): bool
    {
        $data = $this->getUserExtras($permKey, $userId);
        if ($data === null || !empty($data['__has_extras__'])) {
            return false;
        }
        return true;
    }

    /**
     * 获取当前用户拥有的所有权限标识（去重）
     */
    public function permissions(?int $userId = null): array
    {
        return $this->getPermissionKeys($userId);
    }

    /**
     * 检查当前用户是否拥有指定角色
     * @param int|string $roleId  角色 ID 或角色名称
     */
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

    /**
     * 当前用户是否为超级管理员
     */
    public function isSuperAdmin(?int $userId = null): bool
    {
        $roleIds = $this->getRoleIds($userId);
        return in_array(1, $roleIds, true);
    }

    /**
     * 获取用户的所有角色 ID
     */
    protected function getRoleIds(?int $userId = null): array
    {
        $userId = $userId ?? $this->getUserId();

        if ($this->roleIds !== null && $userId === $this->userId) {
            return $this->roleIds;
        }

        $rows = DB::table('admin_roles')
            ->where('admin_id', $userId)
            ->fetchAll();
        $this->roleIds = array_column($rows, 'role_id');
        return $this->roleIds;
    }

    /**
     * 获取用户所有的 perm_key（去重），超级管理员拥有全部权限
     */
    protected function getPermissionKeys(?int $userId = null): array
    {
        $userId = $userId ?? $this->getUserId();

        if ($this->permissionKeys !== null && $userId === $this->userId) {
            return $this->permissionKeys;
        }

        $roleIds = $this->getRoleIds($userId);
        if (empty($roleIds)) {
            $this->permissionKeys = [];
            return [];
        }

        if (in_array(1, $roleIds, true)) {
            $rows = DB::table('permissions')
                ->where('perm_key', '!=', '')
                ->select('perm_key')
                ->fetchAll();
            $this->permissionKeys = array_column($rows, 'perm_key');
            return $this->permissionKeys;
        }

        $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
        $rows = DB::query(
            "SELECT DISTINCT perm_key FROM ?t WHERE role_id IN ({$placeholders})",
            ['roles_permissions', ...$roleIds]
        )->fetchAll();
        $this->permissionKeys = array_column($rows, 'perm_key');
        return $this->permissionKeys;
    }

    /**
     * 获取用户对某个权限的子权限映射
     * @return array|null
     *   null                                    = 没有该权限
     *   ['__has_extras__'=>false] + data        = 权限无 extras 定义 → 拥有全部
     *   ['__has_extras__'=>true] + data (空)    = 有 extras 但未指派 → 无子权限
     *   ['__has_extras__'=>true, 'view'=>true]  = 指派的子权限
     */
    protected function getUserExtras(string $permKey, ?int $userId = null): ?array
    {
        $userId = $userId ?? $this->getUserId();

        if ($this->isSuperAdmin($userId)) {
            return ['__has_extras__' => false];
        }

        if ($this->userExtrasCache !== null && $userId === $this->userId) {
            return $this->userExtrasCache[$permKey] ?? null;
        }

        $roleIds = $this->getRoleIds($userId);
        $this->userExtrasCache = [];

        if (empty($roleIds)) {
            return null;
        }

        $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
        $rows = DB::query(
            "SELECT perm_key, extras FROM ?t WHERE role_id IN ({$placeholders})",
            ['roles_permissions', ...$roleIds]
        )->fetchAll();

        foreach ($rows as $row) {
            $pk = $row['perm_key'];
            $extraStr = $row['extras'] ?? '';

            if (!isset($this->userExtrasCache[$pk])) {
                $this->userExtrasCache[$pk] = [];
            }

            if (!empty($extraStr)) {
                $parts = explode(',', $extraStr);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if ($part !== '') {
                        $this->userExtrasCache[$pk][$part] = true;
                    }
                }
            }
            // $extraStr 为空时：此角色未指派任何子权限
            // 不往 this->userExtrasCache[$pk] 里加任何 key
            // 等其他角色的 extras 合并进来
        }

        // 对每个 perm_key，补上 __has_extras__ 标记
        // 需要批量查 permissions 表确认哪些权限定义了 extras
        if (!empty($this->userExtrasCache)) {
            $pkList = array_keys($this->userExtrasCache);
            $pkPlaceholders = implode(',', array_fill(0, count($pkList), '?'));
            $permRows = DB::query(
                "SELECT perm_key, extras FROM ?t WHERE perm_key IN ({$pkPlaceholders})",
                ['permissions', ...$pkList]
            )->fetchAll();

            $permDefinedExtras = [];
            foreach ($permRows as $pr) {
                $prPk = $pr['perm_key'];
                $prExtras = json_decode($pr['extras'] ?? '{}', true);
                $permDefinedExtras[$prPk] = !empty($prExtras);
            }

            foreach ($this->userExtrasCache as $pk => &$data) {
                $data['__has_extras__'] = $permDefinedExtras[$pk] ?? false;
            }
            unset($data);
        }

        return $this->userExtrasCache[$permKey] ?? null;
    }

    /**
     * 获取当前用户按角色分组的权限详情（含 extras）
     * @return array [role_id => [perm_key => [extras]]]
     */
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
