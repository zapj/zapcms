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


