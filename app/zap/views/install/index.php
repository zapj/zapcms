<?php
\zap\Asset::library('bootstrap');
$this->layout('install/layout');
?>

<div class="container">
    <main>
        <div class="py-5 text-center">
            <img class="d-block mx-auto mb-4" src="<?php echo base_url('/assets/admin/img/zap_logo_green_rgb.svg') ?>" alt="" width="150" >
            <h6>ZAP CMS安装向导</h6>

        </div>

        <div class="row g-5 justify-content-center">

            <div class="col-md-7 col-lg-8 ">
                <div class="card">
                    <h5 class="card-header">请先阅读使用协议</h5>
                    <div class="card-body overflow-y-scroll text-body-secondary" style="height: 400px">
                        <p class="card-text">
                            感谢您选择领格内容管理系统（以下简称ZapCMS），ZapCMS主要目标是帮助企业建立中小型门户网站的解决方案，基于 PHP + MySQL 的技术开发，全部源码开放。<br/>
                            ZapCMS 的官方网址是： https://www.zap.cn/cms<br/>
                            为了使您正确并合法的使用本软件，请您在使用前务必阅读清楚下面的协议条款：<br/>
                        </p>
                        <ol class="">
                            <li>本授权协议适用且仅适用于 ZapCMS 系统，ZapCMS官方对本授权协议拥有最终解释权。</li>
                            <li>协议许可的权利
                                <ul>
                                    <li>您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用。</li>
                                    <li>您可以在协议规定的约束和限制范围内修改 ZapCMS 源代码或界面风格以适应您的网站要求。</li>
                                    <li>您拥有使用本软件构建的网站全部内容所有权，并独立承担与这些内容的相关法律义务。</li>
                                    <li>获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持内容，自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权力，相关意见将被作为首要考虑，但没有一定被采纳的承诺或保证。</li>
                                </ul>
                            </li>

                            <li>协议规定的约束和限制
                                <ul>
                                    <li>未获商业授权之前，必须保留网站底部ZapCMS所有权信息，不得删除、隐藏等否则视为侵权追究法律责任。购买商业授权请登陆www.zap.cn了解最新说明。</li>
                                    <li>未经官方许可，不得对本软件或与之关联的商业授权进行出租、出售。</li>
                                    <li>不管您的网站是否整体使用 ZapCMS ，还是部份栏目使用 ZapCMS，在网站主页上必须保留ZapCMS相关版权信息链接。</li>
                                    <li>未经官方许可，禁止在 ZapCMS 的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。</li>
                                    <li>如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。</li>
                                </ul>
                            </li>
                            <li>有限担保和免责声明
                                <ul>
                                    <li>本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。</li>
                                    <li>用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺对免费用户提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任。</li>
                                    <li>电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始确认本协议并安装DedeCMS，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。</li>
                                    <li>禁止法律法规禁止的行业使用本系统搭建站点。</li>

                                </ul>

                            </li>

                        </ol>
                        <p>版权所有 (c)2014-2023，Zap.cn/CMS 保留所有权利。<br/>
                        协议发布时间：2023年9月13日<br/>
                        版本最新更新：2023年9月13日 By Zap.cn<br/>
                        </p>
                    </div>
                    <div class="card-footer text-center">
                        <a href="<?php echo url_action('Install@database') ?>" class="btn btn-primary">同意 继续安装</a>
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