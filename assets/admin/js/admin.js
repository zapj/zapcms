$(function(){
    const navbarSideCollapse = document.querySelector('#navbarSideCollapse');
    if (navbarSideCollapse !== null) {
        navbarSideCollapse.addEventListener('click', () => {
            document.querySelector('.offcanvas-collapse').classList.toggle('open')
        })
    }

});


const bgPrimary = 'text-bg-primary';
const bgSecondary = 'text-bg-secondary';
const bgSuccess = 'text-bg-success';
const bgDanger = 'text-bg-danger';
const bgWarning = 'text-bg-warning';
const bgInfo = 'text-bg-info';
const bgDark = 'text-bg-dark';

const Toast_Pos_TopCenter = 'topCenterToast';
const Toast_Pos_TopRight = 'topRightToast';
const Toast_Pos_Center = 'centerToast';
const ZapToast = {
    alert:function(msg,params){
        const defaultParams = {
            delay:5000,
            bgColor:bgSuccess,
            position:Toast_Pos_TopCenter,
            callback:function(){}
        }
        if(params === undefined){
            params = {}
        }
        params = Object.assign(defaultParams,params);
        const toastTpl = '<div class="toast align-items-center ' + params.bgColor + '" '+
            'data-bs-config=\'{"delay":'+params.delay+'}\'>' +
            '<div class="d-flex">' +
            '<div class="toast-body">' + msg + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
            '</div>' +
            '</div>';
        const toastContainer = document.getElementById(params.position);
        toastContainer.innerHTML = toastTpl;

        const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastContainer.firstChild)
        toastBootstrap.show()
        toastContainer.firstChild.addEventListener('hidden.bs.toast', params.callback)
    }
};




// ZAP Modal

const ZapModal = {
    loadding:function(color){
        if(color === undefined){
            color = 'text-success';
        }
        return '<div class="spinner-border '+color+'" role="status">\n' +
            '  <span class="visually-hidden">Loading...</span>\n' +
            '</div>';
    },
    show:function(params){
        const defaultParams = {
            title:'',
            content:'',
            callback:function(){}
        }
        if(params === undefined){
            params = {}
        }
        params = Object.assign(defaultParams,params);


    },
    create:function(params,replaced){
        const defaultParams = {
            title:null,
            content:'',
            id:'defaultZapModal',
            callback:function(){},
            keyboard:true,
            focus:true,
            backdrop:'',
            buttons:null,
            url:false,
            dialog_class:'modal-dialog-scrollable modal-dialog-centered modal-lg',
            header_class:'bg-dark text-white',
            body_class:'',
        }
        if(replaced === undefined){replaced = false;}
        /**
         *  buttons:[{title:"test",callback:function(){},close:true}]
         */
        if(params === undefined){
            params = {}
        }
        params = Object.assign(defaultParams,params);
        if($('#' + params.id).length > 0 && replaced === false){
            return new bootstrap.Modal('#' + params.id);
        }
        if(!params.backdrop){params.backdrop = ' data-bs-backdrop="static" data-bs-keyboard="false" ';}
        var modalTpl = '<div class="modal fade" id="'+params.id+'" '+params.backdrop+' tabindex="-1" aria-labelledby="'+params.id+'Label" aria-hidden="true">\n' +
            '  <div class="modal-dialog '+params.dialog_class+'">\n' +
            '    <div class="modal-content">\n' ;
        if(params.title !== null){
            modalTpl += '<div class="modal-header '+params.header_class+' pt-2 pb-2">\n' +
                '        <h1 class="modal-title fs-5" id="'+params.id+'Label">'+params.title+'</h1>\n' +
                '        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>\n' +
                '      </div>\n';
        }
        modalTpl += '<div class="modal-body '+params.body_class+'">\n' +
                    params.content +
            '      </div>\n' ;
        if(params.buttons !== null){
            modalTpl += '<div class="modal-footer">\n' ;
            for (const button in params.buttons) {
                btn = params.buttons[button];
                index = parseInt(button) + 1;
                if(btn.title === undefined){btn.title = '';}
                if(btn.class === undefined){btn.class = 'btn-primary';}
                if(btn.close){
                    modalTpl += '<button type="button" data-index="'+index+'" class="btn btn-sm '+btn.class+'" data-bs-dismiss="modal">'+btn.title+'</button>\n' ;
                }else{
                    modalTpl += '<button type="button" data-index="'+index+'" class="btn btn-sm '+btn.class+'" >'+btn.title+'</button>\n' ;
                }
            }
        modalTpl +=    '      </div>\n';
        }
        modalTpl +=  '    </div>\n' +
            '  </div>\n' +
            '</div>';
        if(replaced===true){
            $('#' + params.id).remove();
        }
        if($('#' + params.id).length === 0){
            $(document.body).append(modalTpl);
        }
        if(params.url!==false){
            $('#' + params.id + ' .modal-body').load(params.url,params.callback);
        }
        $('#' + params.id + ' .modal-footer > button').on('click',function (e) {
            const btnName = 'btn'+$(e.target).data('index');
            if(params[btnName] !== undefined){
                callback = params[btnName];
                callback(e);
            }
        });
        return new bootstrap.Modal('#' + params.id);
    },


};

//https://layui.dev/2.7/docs/modules/layer.html
var ZapFinder = {
    open: function(url){
        layer.open({
            type: 2, // page 层类型
            // area: ['500px', '300px'],
            title: 'ZAP Finder',
            shade: 0.6, // 遮罩透明度
            shadeClose: true, // 点击遮罩区域，关闭弹层
            maxmin: true, // 允许全屏最小化
            anim: 0, // 0-6 的动画形式，-1 不开启
            content: url
        });
    }
}

var Zap = {
    loadding:function(title,icon){
        return layer.open({
            title:false,
            closeBtn:false,
            btn:false,
            content: '<div class="d-flex justify-content-center">\n' +
                '  <div class="spinner-border text-success" role="status">\n' +
                '    <span class="visually-hidden">Loading...</span>\n' +
                '  </div>\n' +
                '</div>'+
                '<div class="text-center">'+title+'</div>'
        });
    },
    closeLayer:function(index){
        layer.close(index)
    },
    closeAllLayer:function(){
        layer.closeAll()
    },
    EnableToolTip:function (){
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    },


}

function ZapFaIcons(target,callback){
    const m = ZapModal.create({
        id:'faIcons',
        title: 'FA ICONS',
        content:ZapModal.loadding(),
        backdrop:false,
        url: ZAP_BASE_URL + '/finder/faicons',
        callback:function(){
            const $callback = callback
            const $targetArray = typeof target === 'string' ? [target] : target;
            $('#faIcons .modal-body').on('click','button',(e)=>{
                let className = 'fa fa-square-poll-horizontal';
                if(e.target.tagName === 'I'){
                    className = e.target.className
                }else if(e.target.tagName === 'INPUT' || e.target.tagName ===  'BUTTON'){
                    className = e.target.querySelector('i').className
                }

                if(typeof $callback === 'function'){
                    $callback($targetArray,className)
                }
        // console.log($($target))
                $targetArray.forEach(($target)=>{
                    if($($target).is('input')){
                        $($target).val(className);
                    }else if($($target).is('i')){
                        $($target).prop('class',className);
                    }
                })

                m.hide();
            })
        }
    },true)
    m.show();

}



const loadScript = (fileUrl, async = true, type = "text/javascript") => {
    return new Promise((resolve, reject) => {
        try {
            const scriptEle = document.createElement("script");
            scriptEle.type = type;
            scriptEle.async = async;
            scriptEle.src =fileUrl;

            scriptEle.addEventListener("load", (ev) => {
                resolve({ status: true });
            });

            scriptEle.addEventListener("error", (ev) => {
                reject({
                    status: false,
                    message: `Failed to load the script ${fileUrl}`
                });
            });

            document.head.appendChild(scriptEle);
        } catch (error) {
            reject(error);
        }
    });
};

