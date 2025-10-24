let menubar = document.querySelector('#menu-bar')
let mynav = document.querySelector('.barra-navegacion')



menubar.onclick = ()=>{
    menubar.classList.toggle('fa-times')
    mynav.classList.toggle('active')
}

