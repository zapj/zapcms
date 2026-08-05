<?php

use zap\cms\NodeType;

$this->layout('layouts/common');
?>
<main class="container">
    <div class="row g-3">
        <div class="col-12">
            <h5 class="page-title mb-3"><i class="fa fa-dashboard me-2 text-primary"></i>控制面板</h5>
        </div>
        
        <!-- 单页管理 -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="fa fa-file-pen me-2"></i>单页管理</span>
                    <a href="<?php echo url_action("Node@page"); ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-plus me-1"></i>管理
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <?php foreach($pages as $page){ ?>
                        <div class="col-4 col-md-2">
                            <a href="<?php echo url_action("Node@page/edit/{$page['id']}"); ?>" class="text-decoration-none">
                                <div class="p-3 bg-light rounded text-center hover-shadow">
                                    <i class="fa fa-file text-primary mb-2 fs-4 d-block"></i>
                                    <small class="text-success fw-medium d-block text-truncate"><?php echo $page['title'] ?></small>
                                </div>
                            </a>
                        </div>
                        <?php } ?>
                        <?php if(empty($pages)): ?>
                        <div class="col-12 text-center text-muted py-3">
                            <i class="fa fa-inbox me-2"></i>暂无单页内容
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 网站信息 -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="fa fa-sitemap me-2"></i>网站信息</span>
                    
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <?php 
                        $typeIcons = [
                            'article' => 'fa-newspaper',
                            'news' => 'fa-bullhorn',
                            'product' => 'fa-box-open',
                            'photo' => 'fa-images',
                            'video' => 'fa-video',
                            'download' => 'fa-download',
                            'job' => 'fa-briefcase',
                            'faq' => 'fa-question-circle',
                        ];
                        $typeColors = [
                            'article' => 'primary',
                            'news' => 'success',
                            'product' => 'warning',
                            'photo' => 'info',
                            'video' => 'danger',
                            'download' => 'secondary',
                            'job' => 'dark',
                            'faq' => 'success',
                        ];
                        foreach($node_types_statistics as $type=>$count){ 
                            $icon = $typeIcons[$type] ?? 'fa-folder';
                            $color = $typeColors[$type] ?? 'primary';
                            $title = NodeType::getTitle($type);
                        ?>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <a href="<?php echo url_action("Node@{$type}"); ?>" class="text-decoration-none">
                                <div class="stat-card p-3 bg-light rounded text-center h-100 hover-shadow">
                                    <div class="stat-icon mb-2">
                                        <span class="badge bg-<?php echo $color; ?> p-2 rounded-circle">
                                            <i class="fa <?php echo $icon; ?> fa-lg"></i>
                                        </span>
                                    </div>
                                    <div class="stat-count h5 mb-1 text-<?php echo $color; ?>"><?php echo $count ?></div>
                                    <small class="text-muted d-block text-truncate" title="<?php echo $title; ?>"><?php echo $title ?></small>
                                </div>
                            </a>
                        </div>
                        <?php } ?>
                        <?php if(empty($node_types_statistics)): ?>
                        <div class="col-12 text-center text-muted py-4">
                            <i class="fa fa-inbox me-2"></i>暂无内容分类
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 系统环境 -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fa fa-server me-2"></i>系统环境
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="w-50"><small class="text-muted">系统语言</small></td>
                                    <td><?php echo req()->getPreferredLanguage(); ?></td>
                                </tr>
                                <tr>
                                    <td><small class="text-muted">站点模版</small></td>
                                    <td><span class="badge bg-primary"><?php echo option('website.theme','basic'); ?></span></td>
                                </tr>
                                <tr>
                                    <td><small class="text-muted">上传限制</small></td>
                                    <td><?php echo ini_get('upload_max_filesize'); ?></td>
                                </tr>
                                <tr>
                                    <td><small class="text-muted">内存限制</small></td>
                                    <td><?php echo ini_get('memory_limit'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 系统信息 -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fa fa-info-circle me-2"></i>系统信息
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="w-50"><small class="text-muted">ZapCMS</small></td>
                                    <td>v<?php echo ZAP_CMS_VERSION.'-'.ZAP_CMS_RELEASE_DATE; ?></td>
                                </tr>
                                <tr>
                                    <td><small class="text-muted">PHP 版本</small></td>
                                    <td><?php echo PHP_VERSION; ?></td>
                                </tr>
                                <tr>
                                    <td><small class="text-muted"><?php $driver = \zap\DB::getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME); echo strtoupper($driver); ?></small></td>
                                    <td><?php 
                                        if($driver == 'mysql'){
                                            echo 'v'.\zap\DB::value("SELECT VERSION()");
                                        }else if($driver == 'sqlite'){
                                            echo 'v3.x';
                                        }
                                    ?></td>
                                </tr>
                                <tr>
                                    <td><small class="text-muted">Web Server</small></td>
                                    <td><small><?php echo \zap\http\Request::server('SERVER_SOFTWARE'); ?></small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.page-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
}

.hover-shadow {
    transition: all 0.2s ease;
}

.hover-shadow:hover {
    background: #e9ecef !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

/* 统计卡片样式 */
.stat-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100px;
}

.stat-card .stat-icon {
    line-height: 1;
}

.stat-card .stat-count {
    font-weight: 700;
    line-height: 1.2;
}

/* 绿色主题色 */
.bg-green {
    background-color: #10b981 !important;
    color: #fff;
}

.text-green {
    color: #10b981 !important;
}

@media (max-width: 768px) {
    .page-title {
        font-size: 1.1rem;
    }
    
    .card {
        margin-bottom: 1rem;
    }
    
    .card-body {
        padding: 0.875rem;
    }
    
    .table {
        font-size: 0.85rem;
    }
    
    .table td {
        padding: 0.5rem 0.75rem;
    }
    
    .hover-shadow {
        padding: 0.75rem !important;
    }
    
    .hover-shadow .fs-4 {
        font-size: 1.25rem !important;
    }
    
    /* 统计卡片移动端适配 */
    .stat-card {
        min-height: 80px;
        padding: 0.75rem !important;
    }
    
    .stat-card .badge.p-2 {
        padding: 0.5rem !important;
    }
    
    .stat-card .stat-count {
        font-size: 1.1rem;
    }
}
</style>
