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

    /**
     * 设置当前用户上下文
     */
    public function setUser(?int $userId): self
    {
        $this->userId = $userId;
        $this->roleIds = null;
        $this->permissionKeys = null;
        $this->rolePermissions = null;
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

    /**
     * 检查当前用户是否拥有指定权限
     * @param string $permKey 权限标识（perm_key）
     * @return bool
     */
    public function check($permKey): bool
    {
        if (empty($permKey)) {
            return false;
        }

        $permissionKeys = $this->getPermissionKeys();
        return in_array($permKey, $permissionKeys, true);
    }

    /**
     * 获取当前用户拥有的所有角色
     * @param int|null $userId
     * @return array
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
     * 获取当前用户拥有的所有权限标识（去重）
     * @param int|null $userId
     * @return array
     */
    public function permissions(?int $userId = null): array
    {
        return $this->getPermissionKeys($userId);
    }

    /**
     * 检查当前用户是否拥有指定角色
     * @param int|string $roleId 角色 ID 或角色名称
     * @param int|null $userId
     * @return bool
     */
    public function hasRole($roleId, ?int $userId = null): bool
    {
        $userId = $userId ?? $this->getUserId();

        if (is_numeric($roleId)) {
            $roleIds = $this->getRoleIds($userId);
            return in_array((int)$roleId, $roleIds, true);
        }

        // 按角色 name 查询
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

        // 超级管理员 role_id=1 拥有全部权限
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
