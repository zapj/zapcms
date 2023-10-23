<?php
$this->layout('layout');
?>

<div class="container">
    <main>
        <div class="py-5 text-center">
            <img class="d-block mx-auto mb-4" src="../assets/admin/img/zap_logo_green_rgb.svg" alt="" width="150" >
            <h6>ZAP CMS安装向导</h6>

        </div>

        <div class="row g-5 justify-content-center">

            <div class="col-md-7 col-lg-8 ">
                <div class="card">
                    <h5 class="card-header">检查服务器环境</h5>
                    <div class="card-body p-0">
                        <form class="needs-validation" novalidate>
                            <table class="table table-bordered table-hover">

                                <tbody>
                                <tr>
                                    <td>PHP 版本</td>
                                    <th><?php echo PHP_VERSION; ?>
                                        <?php
                                        if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
                                            echo '<strong class="fw-bold fs-5 text-success">√</strong>';
                                        } else {
                                            echo '<strong class="fw-bold fs-5 text-danger">╳ 最低版本不能低于 7.4</strong>';
                                        }
                                        ?>
                                    </th>

                                </tr>
                                <tr>
                                    <td>PHP PDO</td>
                                    <th><?php $allDrivers = PDO::getAvailableDrivers();
                                        echo join('/', $allDrivers) ?>

                                        <?php
                                        if (!in_array('mysql', $allDrivers) &&
                                            !in_array('pgsql', $allDrivers) &&
                                            !in_array('sqlite', $allDrivers)) {
                                            echo '<strong class="fw-bold fs-5 text-danger">╳ 仅支持PDO（pgsql/sqlite/mysql）</strong>';
                                        } else {

                                            echo '<strong class="fw-bold fs-5 text-success">√</strong>';
                                        }
                                        ?>
                                    </th>

                                </tr>
                                <tr>
                                    <td>PHP GD扩展</td>
                                    <th><?php echo function_exists('gd_info') ? current(gd_info()) : '不支持'; ?>
                                        <?php
                                        if (!function_exists('gd_info')) {
                                            echo '<strong class="fw-bold fs-5 text-danger">╳ 不支持 gd 扩展，无法处理图片</strong>';
                                        } else {

                                            echo '<strong class="fw-bold fs-5 text-success">√</strong>';
                                        }
                                        ?>
                                    </th>

                                </tr>
                                <tr>
                                    <td colspan="2">目录写入权限</td>
                                </tr>

                                <tr>
                                    <td>storage</td>
                                    <th><?php if ((is_dir(base_path('/storage')) && is_writeable(base_path('/storage')))) {
                                            echo '<strong class="fw-bold fs-5 text-success">√</strong>';
                                        } else {
                                            echo '<strong class="fw-bold fs-5 text-danger">╳ </strong>';
                                        } ?></th>

                                </tr>

                                <tr>
                                    <td>var</td>
                                    <th><?php if ((is_dir(base_path('/var')) && is_writeable(base_path('/var')))) {
                                            echo '<strong class="fw-bold fs-5 text-success">√</strong>';
                                        } else {
                                            echo '<strong class="fw-bold fs-5 text-danger">╳ </strong>';
                                        } ?></th>

                                </tr>

                                <tr>
                                    <td>themes</td>
                                    <th><?php if ((is_dir(base_path('/themes')) && is_writeable(base_path('/themes')))) {
                                            echo '<strong class="fw-bold fs-5 text-success">√</strong>';
                                        } else {
                                            echo '<strong class="fw-bold fs-5 text-danger">╳ </strong>';
                                        } ?></th>

                                </tr>

                                </tbody>

                            </table>


                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="index.php?action=database" class="btn btn-success">下一步</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="my-5 pt-5 text-body-secondary text-center text-small">
        <p class="mb-1">&copy; 2023 Zap.CN</p>
        <ul class="list-inline">
            <li class="list-inline-item"><a href="#">Privacy</a></li>
            <li class="list-inline-item"><a href="#">Terms</a></li>
            <li class="list-inline-item"><a href="#">Support</a></li>
        </ul>
    </footer>
</div>