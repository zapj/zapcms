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

$(function(){
    const navbarSideCollapse = document.querySelector('#navbarSideCollapse');
    if (navbarSideCollapse !== null) {
        navbarSideCollapse.addEventListener('click', () => {
            document.querySelector('.offcanvas-collapse').classList.toggle('open')
        })
    }

    $(document.body).on('click','button[data-zap-toggle=\"image\"]',function(e){
        e.preventDefault();
        Zap.finder({target:$(this).data('zap-target'),callback:$(this).data('zap-callback'),size:$(this).data('zap-size')});
        window.zapFinder.target = e.target
        if(typeof Finder_Show !== "undefined"){
            Finder_Show(e)
        }

    });

});



const ZapToast = {
    alert:function(msg,params){
        const defaultParams = {
            delay:1000,
            bgColor:bgSuccess,
            position:Toast_Pos_TopCenter,
            callback:function(){}
        }
        if(params === undefined){
            params = {}
        }
        params = Object.assign(defaultParams,params);
        const toastTpl = `<div class="toast align-items-center ${params.bgColor}" data-bs-config='{"delay":${params.delay}}'>
            <div class="d-flex">
            <div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>`;
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
        return `<div class="spinner-border ${color}" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>`;
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
            header_class:'bg-success text-white',
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
        modalTpl += `<div class="modal-body ${params.body_class}">${params.content}</div>` ;
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

var Zap = {
    loading:function(title){
        if(title===undefined){
            title = '加载中，请稍后...'
        }
        const content = `<div class="d-flex justify-content-center">
          <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div class="text-center">${title}</div>`;
        const loadModal = new ZapDialog({content:content,backdrop:true});
        loadModal.show()
        return loadModal;
    },
    EnableToolTip:function (){
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    },
    AjaxPost:function(settings){
        settings['method'] = 'post';
        return $.ajax(settings);
    },
    alert:function(msg,title){
        const alert = new ZapDialog({content:msg,title:title,backdrop:false});
        alert.show()
    },
    finder:function(options){
        if(window.zapFinder){
            window.zapFinder.show()
            return window.zapFinder;
        }
        options = options || {};
        options.title = options.title  || "文件管理器";
        options.path = options.path || '';
        options.target = options.target || '';
        options.size = options.size || '';
        options.callback = options.callback || '';
        window.zapFinder = new ZapDialog({
            id:'zapFinder',
            url:ZAP_BASE_URL + '/finder/list?initialize=true&path=' + options.path +'&target='+encodeURIComponent(options.target) +  '&callback='+options.callback + '&size='+options.size,
            title:options.title,
            backdrop:false,
            dialogClass:'modal-xl',
            context:options.context
        });
        window.zapFinder.show()
        return window.zapFinder;
    }

}

function ZapDialog(settings){
    const defaultSettings = {
        id:null,
        title:null,
        closeBtn: true,
        header:true,
        footer:true,
        content:'',
        loading:true,
        replace:false,
        backdrop:true,
        dialogClass:'',
        modalClass:'',
        headerClass:'',
        contentClass:'',
        bodyClass:'',
        footerClass:'',
        url:null,
        buttons:[],
        events:{},
        callback:function(){}
    };
    this.settings = Object.assign(defaultSettings,settings);
    this.target = null;
    if(this.settings.id === null) this.settings.id = Zap.RandID();
    this.modalBody = function(body){
        if(this.settings.url !== null && this.settings.loading === true && body === ''){
            body = `<div class="spinner-border text-success" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>`;
        }
        return `<div class="modal-body ${this.settings.bodyClass}">${body}</div>`;
    }
    this.modalFooter = function(){
        if(this.settings.buttons.length === 0) return '';
        let buttons = [];
        for (let btnIndex in this.settings.buttons){
            btn = this.settings.buttons[btnIndex];
            btn.class = btn.class || ' class="btn btn-success btn-sm" ';
            btn.title = btn.title || '默认按钮';
            btn.close = btn.close === undefined ? ' data-bs-dismiss="modal" ' :'';
            buttons.push(`<button type="button" data-index="${btnIndex}" class="${btn.class}" ${btn.close}>${btn.title}</button>`)
        }
        let buttonStr = buttons.join();
        return `<div class="modal-footer ${this.settings.footerClass}">${buttonStr}</div>`;
    }

    this.modalHeader = function(value){
        if(this.settings.title === null || this.settings.title === undefined) return '';
        let closeBtn = `<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>`;
        if(this.settings.closeBtn === false){
            closeBtn = '';
        }else if(this.settings.closeBtn !== true){
            closeBtn = this.settings.closeBtn;
        }
        return `<div class="modal-header"><h5 class="modal-title">${value}</h5>${closeBtn}</div>`;
    }
    this.render = function(){

        const header = this.modalHeader(this.settings.title)
        const body = this.modalBody(this.settings.content)
        const footer = this.modalFooter()
        const backdrop = this.settings.backdrop === true ? `data-bs-backdrop="static" data-bs-keyboard="false"`:'';
        return `<div class="modal  ${this.settings.modalClass}" tabindex="-1" id="${this.settings.id}" ${backdrop} aria-hidden="true">    
                  <div class="modal-dialog ${this.settings.dialogClass}">
                    <div class="modal-content ${this.settings.contentClass}">
                      ${header}
                      ${body}
                      ${footer}
                    </div>
                  </div>
                </div>`;
    }
    this.create=function(){
        q = document.querySelector('#'+this.settings.id);
        if(this.settings.replace === true && q !==null){
            q.remove();
        }else if(this.settings.replace === false && q !==null){
            this.modal = new bootstrap.Modal(q)
            return;
        }
        let modalNode = Zap.createElement(this.render());
        document.body.appendChild(modalNode);
        this.modal = new bootstrap.Modal(document.getElementById(this.settings.id))
        for (const eventKey in this.settings.events) {
            modalNode.addEventListener(eventKey,this.settings.events[eventKey]);
        }
        if(this.settings.url !== null){
            $('#'+this.settings.id+' .modal-body').load(this.settings.url,this.settings.callback);
        }
    }
    this.setContent = function (content){
        document.querySelector('#'+this.settings.id+' .modal-body').innerHTML = content;
    }

    this.dispose = function(){
      this.modal.dispose();
        document.querySelector('#'+this.settings.id).remove();
    }

    this.hide = function(){
        this.modal.hide();
    }
    this.show = function(){

        if(this.modal === undefined){
            this.create();
        }
        this.modal.show();
    }
}



Zap.createModal = function(settings){
    return new ZapDialog(settings)
}

Zap.startLoading = function(){

}

Zap.reload=function(settings){
    const defaultSettings = {id:'#zContent',url:null,data:null,callback:function(){}}
    if(settings === undefined){
        settings = {}
    }
    settings = Object.assign(defaultSettings,settings);
    if(settings.url === null){
        settings.url = location.href;
    }
    $(settings.id).load(settings.url,settings.data,settings.callback);
}

Zap.createElement = function(str){
    let child = document.createElement('div');
    child.innerHTML = str;
    return child.firstChild;
}

Zap.RandString = function(len){
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    const charLength = chars.length;
    let result = '';
    for (let i = 0; i < len; i++ ) {
        result += chars.charAt(Math.floor(Math.random() * charLength));
    }
    return result;
}

Zap.RandID = function(){
    const chars = 'abcdefghijklmnopqrstuvwxyz';
    const charLength = chars.length;
    let result = 'ZAP';
    for (let i = 0; i < 5; i++ ) {
        result += chars.charAt(Math.floor(Math.random() * charLength));
    }
    return result + ((new Date()).getTime());
}

Zap.CheckBox_CheckAll = function(el,id){
    $(id).prop('checked', $(el).prop('checked'));
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

