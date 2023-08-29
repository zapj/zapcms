(() => {
    'use strict'
    const navbarSideCollapse = document.querySelector('#navbarSideCollapse');
    if (navbarSideCollapse !== null){
        navbarSideCollapse.addEventListener('click', () => {
            document.querySelector('.offcanvas-collapse').classList.toggle('open')
        })
    }

})()


