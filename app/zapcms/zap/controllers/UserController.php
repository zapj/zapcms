<?php

namespace app\zap\controllers;

use zap\cms\AdminController;
use zap\cms\Auth;
use zap\cms\models\Admin;
use zap\cms\models\AdminLog;
use zap\DB;
use zap\facades\Url;
use zap\http\Request;
use zap\http\Response;
use zap\util\Password;

class UserController extends AdminController
{

    public function index()
    {
        Response::redirect(Url::action('User@profile'));
    }
    /**
     * 设置页 — 集成个人资料 / 修改密码 / 操作记录
     */
    public function profile()
    {
        $user  = Admin::getProfile();
        $tab   = Request::get('tab', 'account');

        // 操作记录分页
        $page     = max(1, (int)Request::get('page', 1));
        $perPage  = 15;
        $total    = AdminLog::countByUser($user['id']);
        $logs     = $total > 0 ? AdminLog::getByUser($user['id'], $perPage, ($page - 1) * $perPage) : [];
        $totalPages = max(1, (int)ceil($total / $perPage));

        $data = [
            'user'        => $user,
            'active_tab'  => $tab,
            'page_title'  => '账户设置',
            'logs'        => $logs,
            'log_page'    => $page,
            'log_total_pages' => $totalPages,
            'log_total'   => $total,
        ];

        view('user.settings', $data);
    }

    // ========== 个人资料更新 ==========

    public function updateProfile()
    {
        if (!Request::isPost()) {
            Response::redirect(Url::action('User@profile'));
        }

        $userId   = Auth::user('id');
        $fullName = trim(Request::post('full_name', ''));
        $email    = trim(Request::post('email', ''));
        $phone    = trim(Request::post('phone_number', ''));

        if ($fullName === '') {
            Response::redirect(Url::action('User@profile', ['tab' => 'account']))->with('error', '姓名不能为空');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::redirect(Url::action('User@profile', ['tab' => 'account']))->with('error', '邮箱格式不正确');
        }

        $data = [
            'full_name'    => $fullName,
            'email'        => $email,
            'phone_number' => $phone,
            'updated_at'   => time(),
        ];
        DB::update('admin', $data, ['id' => $userId]);

        // 更新 session
        $session = session()->get('zapAdmin');
        $session['full_name']    = $fullName;
        $session['email']        = $email;
        $session['phone_number'] = $phone;
        session()->set('zapAdmin', $session);
        app()->set('zapAdmin', $session);

        AdminLog::log('更新个人资料', "姓名: {$fullName}");

        Response::redirect(Url::action('User@profile', ['tab' => 'account']))->with('success', '个人资料更新成功');
    }

    // ========== 头像上传 ==========

    public function uploadAvatar()
    {
        if (!Request::isPost()) {
            Response::redirect(Url::action('User@profile'));
        }

        $userId  = Auth::user('id');
        $uploads = Request::file('avatar');

        if (empty($uploads) || empty($uploads['tmp_name']) || $uploads['error'] !== UPLOAD_ERR_OK) {
            Response::redirect(Url::action('User@profile', ['tab' => 'account']))->with('error', '请选择要上传的头像');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $uploads['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes, true)) {
            Response::redirect(Url::action('User@profile', ['tab' => 'account']))->with('error', '仅支持 JPG / PNG / GIF / WebP 格式');
        }

        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($uploads['size'] > $maxSize) {
            Response::redirect(Url::action('User@profile', ['tab' => 'account']))->with('error', '头像文件不能超过 2MB');
        }

        $uploadDir = var_path('storage/avatar');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $ext = isset($extMap[$mime]) ? $extMap[$mime] : 'jpg';
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $dest     = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($uploads['tmp_name'], $dest)) {
            Response::redirect(Url::action('User@profile', ['tab' => 'account']))->with('error', '头像保存失败，请重试');
        }

        $avatarUrl = base_url('/var/storage/avatar/' . $filename);

        DB::update('admin', ['avatar_url' => $avatarUrl, 'updated_at' => time()], ['id' => $userId]);

        $session = session()->get('zapAdmin');
        $session['avatar_url'] = $avatarUrl;
        session()->set('zapAdmin', $session);
        app()->set('zapAdmin', $session);

        AdminLog::log('更新头像', '上传了新头像');

        Response::redirect(Url::action('User@profile', ['tab' => 'account']))->with('success', '头像更新成功');
    }

    // ========== 修改密码 ==========

    public function changePassword()
    {
        if (!Request::isPost()) {
            Response::redirect(Url::action('User@profile'));
        }

        $userId      = Auth::user('id');
        $oldPassword = Request::post('old_password', '');
        $newPassword = Request::post('new_password', '');
        $confirmPwd  = Request::post('confirm_password', '');

        if ($oldPassword === '' || $newPassword === '' || $confirmPwd === '') {
            Response::redirect(Url::action('User@profile', ['tab' => 'security']))->with('error', '请填写所有密码字段');
        }

        if (mb_strlen($newPassword) < 6) {
            Response::redirect(Url::action('User@profile', ['tab' => 'security']))->with('error', '新密码至少需要 6 个字符');
        }

        if ($newPassword !== $confirmPwd) {
            Response::redirect(Url::action('User@profile', ['tab' => 'security']))->with('error', '两次输入的新密码不一致');
        }

        $admin = DB::table('admin')->where('id', $userId)->fetch(FETCH_ASSOC);
        if (empty($admin) || !Password::verify($oldPassword, $admin['password'])) {
            Response::redirect(Url::action('User@profile', ['tab' => 'security']))->with('error', '旧密码不正确');
        }

        DB::update('admin', [
            'password'   => Password::hash($newPassword),
            'updated_at' => time(),
        ], ['id' => $userId]);

        AdminLog::log('修改密码', '更新了登录密码');

        Response::redirect(Url::action('User@profile', ['tab' => 'security']))->with('success', '密码修改成功');
    }
}
