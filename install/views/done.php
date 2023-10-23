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
                    <h5 class="card-header">配置完成</h5>
                    <div class="card-body ">
                        <form class="needs-validation" novalidate>
                            <div class="alert alert-success" role="alert">
                                安装完成
                            </div>



                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="../z-admin" target="_blank" class="btn btn-success">控制面板</a>
                        <a href="../" target="_blank" class="btn btn-success">网站首页</a>
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