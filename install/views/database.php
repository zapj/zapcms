<?php
$this->layout('layout');
?>

<div class="container">
    <main>
        <div class="py-5 text-center">
            <img class="d-block mx-auto mb-4" src="../assets/admin/img/zap_logo_green.svg" alt="" width="150" >
            <h6>ZAP CMS安装向导</h6>

        </div>

        <div class="row g-5 justify-content-center">

            <div class="col-md-7 col-lg-8 ">
                <div class="card">
                    <h5 class="card-header">配置数据库</h5>
                    <div class="card-body ">
                        <form class="needs-validation" novalidate>
                            <div class="row g-3">



                                <div class="col-12">
                                    <label for="website-title" class="form-label">网站名称</label>
                                    <input type="email" class="form-control" id="website-title" name="website[title]" placeholder="ZAP CMS" value="ZAP CMS">
                                </div>

                                <div class="col-12">
                                    <label for="website-email" class="form-label">邮箱 <span class="text-body-secondary">(Optional)</span></label>
                                    <input type="email" class="form-control" id="website-email" name="website[email]" placeholder="you@example.com">
                                </div>

                                <div class="col-12">
                                    <label for="db-type" class="form-label">数据库类型</label>
                                    <select id="db-type" name="db[type]"  class="form-select">
                                        <option value="mysql">MySQL / MariaDB</option>
                                        <option value="sqlite" disabled>Sqlite3</option>
                                        <option value="pgsql" disabled>PostgreSQL</option>
                                    </select>
                                </div>

                                <div class="col-4">
                                    <label for="db-user" class="form-label">用户名</label>
                                    <input type="text" class="form-control" id="db-user" name="db[user]" placeholder="数据库用户名 , mysql 默认 root" value="root">
                                </div>
                                <div class="col-4">
                                    <label for="db-password" class="form-label">密码</label>
                                    <input type="text" class="form-control" id="db-password" name="db[password]" placeholder="数据库密码">
                                </div>

                                <div class="col-md-4">
                                    <label for="db-dbname" class="form-label">数据库名称</label>
                                    <input type="text" class="form-control" id="db-dbname" name="db[dbname]" placeholder="数据库名称">
                                </div>


                                <div class="col-md-12 text-black-50 overflow-y-auto" style="height: 100px;">
                                    <hr/>
                                    安装过程<br/>
                                    安装过程<br/>
                                    安装过程<br/>
                                    安装过程<br/>
                                    安装过程<br/>
                                    安装过程<br/>
                                    安装过程<br/>
                                </div>
                            </div>

                            <hr class="my-4">


<!--                            <div class="form-check">-->
<!--                                <input type="checkbox" class="form-check-input" id="save-info">-->
<!--                                <label class="form-check-label" for="save-info"></label>-->
<!--                            </div>-->



                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="index.php?action=done" class="btn btn-success">立刻安装</a>
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