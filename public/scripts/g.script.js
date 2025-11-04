// MENU HAMBURGUESA MEJORADO Y CORREGIDO 
document.addEventListener('DOMContentLoaded', function () {
    const menubar = document.querySelector('#menu-bar');
    const mynav = document.querySelector('.barra-navegacion');
    const body = document.body;

    // Validar que los elementos existan
    if (!menubar || !mynav) return;

    // Crear overlay dinámicamente si no existe
    let overlay = document.querySelector('.nav-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'nav-overlay';
        document.body.appendChild(overlay);
    }

    // Guardar la posición del scroll
    let scrollPosition = 0;

    // Función para prevenir scroll
    const preventScroll = (e) => {
        e.preventDefault();
        e.stopPropagation();
        return false;
    };

    // Función para abrir el menú
    const openMenu = () => {
        scrollPosition = window.pageYOffset;
        
        requestAnimationFrame(() => {
            menubar.classList.add('fa-times');
            mynav.classList.add('active');
            body.classList.add('menu-open');
            overlay.classList.add('active');
            
            body.style.overflow = 'hidden';
            body.style.position = 'fixed';
            body.style.top = `-${scrollPosition}px`;
            body.style.width = '100%';
            
            document.addEventListener('touchmove', preventScroll, { passive: false });
        });
    };

    // FUNCION PARA CERRAR EL MENU
    const closeMenu = () => {
        menubar.classList.remove('fa-times');
        mynav.classList.remove('active');
        body.classList.remove('menu-open');
        overlay.classList.remove('active');
        
        setTimeout(() => {
            body.style.overflow = '';
            body.style.position = '';
            body.style.top = '';
            body.style.width = '';
            window.scrollTo(0, scrollPosition);
            document.removeEventListener('touchmove', preventScroll);
        }, 300); 
    };

    // TOGGLE DEL MENU 
    menubar.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (mynav.classList.contains('active')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    overlay.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        closeMenu();
    });

    document.addEventListener('click', function (e) {
        const isClickInsideMenu = mynav.contains(e.target);
        const isClickOnMenuButton = menubar.contains(e.target);
        
        if (!isClickInsideMenu && !isClickOnMenuButton && mynav.classList.contains('active')) {
            closeMenu();
        }
    });

    mynav.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    const navLinks = mynav.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            closeMenu();
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && mynav.classList.contains('active')) {
            closeMenu();
        }
    });

    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.innerWidth > 768 && mynav.classList.contains('active')) {
                closeMenu();
            }
        }, 250);
    });
});

// ====================================================================
// CONTADOR DE NOTIFICACIONES CON MEJORAS
// ====================================================================
(function () {
    var BASE_URL = window.baseUrl || (window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/'));
    var contadorAnterior = 0;
    var tituloOriginal = document.title;

    /**
     * Reproduce sonido de notificación
     */
    function reproducirSonidoNotificacion() {
        // Crear un tono simple usando Web Audio API
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (e) {
            console.log('No se pudo reproducir el sonido de notificación');
        }
    }

    /**
     * Actualiza el título de la página con el contador
     */
    function actualizarTituloPagina(count) {
        if (count > 0) {
            document.title = `(${count}) ${tituloOriginal}`;
        } else {
            document.title = tituloOriginal;
        }
    }

    /**
     * Actualiza el contador del badge en el header
     */
    function actualizarContadorHeader() {
        var url = BASE_URL + 'controllers/NotificacionesAjaxController.php?action=contador';
        
        fetch(url, { 
            credentials: 'same-origin',
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(function (res) { 
            if (!res.ok) throw new Error('HTTP error! status: ' + res.status);
            return res.json(); 
        })
        .then(function (data) {
            var badge = document.getElementById('notification-count');
            if (!badge) return;

            if (data && data.success && data.totalGeneral !== undefined) {
                var total = parseInt(data.totalGeneral);
                
                // Detectar si hay nuevas notificaciones
                if (total > contadorAnterior && contadorAnterior > 0) {
                    reproducirSonidoNotificacion();
                    badge.classList.add('badge-bounce');
                    setTimeout(() => badge.classList.remove('badge-bounce'), 500);
                }
                
                contadorAnterior = total;
                
                if (total > 0) {
                    badge.textContent = total;
                    badge.style.display = 'flex';
                    badge.classList.add('show');
                } else {
                    badge.textContent = '';
                    badge.style.display = 'none';
                    badge.classList.remove('show');
                }
                
                // Actualizar título de la página
                actualizarTituloPagina(total);
            } else {
                badge.textContent = '';
                badge.style.display = 'none';
                badge.classList.remove('show');
                actualizarTituloPagina(0);
            }
        })
        .catch(function (error) { 
            console.warn('Error al actualizar contador de notificaciones:', error);
        });
    }

    /**
     * Función pública para actualizar el badge manualmente
     */
    window.actualizarBadgeHeader = function (count) {
        var badge = document.getElementById('notification-count');
        if (!badge) return;
        
        var total = parseInt(count) || 0;
        
        if (total > 0) {
            badge.textContent = total;
            badge.style.display = 'flex';
            badge.classList.add('show');
            badge.classList.add('badge-bounce');
            setTimeout(() => badge.classList.remove('badge-bounce'), 500);
        } else {
            badge.textContent = '';
            badge.style.display = 'none';
            badge.classList.remove('show');
        }
        
        actualizarTituloPagina(total);
    };

    /**
     * Función para forzar actualización desde el backend
     */
    window.refrescarContadorNotificaciones = function() {
        actualizarContadorHeader();
    };

    // Ejecutar al cargar la página
    document.addEventListener('DOMContentLoaded', function () {
        actualizarContadorHeader();
        setInterval(actualizarContadorHeader, 30000);
    });

    // Si la página ya está cargada, ejecutar inmediatamente
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(actualizarContadorHeader, 100);
    }
})();