<?php

namespace zap\http;

/**
 * RESTful 控制器基类
 *
 * 提供 7 个标准 REST 动作的默认实现（返回 JSON）。
 * 子类覆盖对应方法即可。
 */
abstract class RestController extends Controller
{
    /**
     * GET /resource — 列表
     */
    public function index()
    {
        return $this->json(['message' => 'index']);
    }

    /**
     * GET /resource/create — 创建表单
     */
    public function create()
    {
        return $this->json(['message' => 'create']);
    }

    /**
     * POST /resource — 存储
     */
    public function save()
    {
        return $this->json(['message' => 'save']);
    }

    /**
     * GET /resource/{id} — 详情
     */
    public function show()
    {
        return $this->json(['message' => 'show']);
    }

    /**
     * GET /resource/{id}/edit — 编辑表单
     */
    public function edit()
    {
        return $this->json(['message' => 'edit']);
    }

    /**
     * PUT/PATCH /resource/{id} — 更新
     */
    public function update()
    {
        return $this->json(['message' => 'update']);
    }

    /**
     * DELETE /resource/{id} — 删除
     */
    public function destroy()
    {
        return $this->json(['message' => 'destroy']);
    }
}
