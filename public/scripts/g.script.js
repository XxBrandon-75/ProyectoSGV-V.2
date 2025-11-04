// MENU HAMBURGUESA MEJORADO Y CORREGIDO
document.addEventListener("DOMContentLoaded", function () {
  const menubar = document.querySelector("#menu-bar");
  const mynav = document.querySelector(".barra-navegacion");
  const body = document.body;

  // Validar que los elementos existan
  if (!menubar || !mynav) return;

  // Crear overlay dinámicamente si no existe
  let overlay = document.querySelector(".nav-overlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.className = "nav-overlay";
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
    // Guardar posición actual del scroll
    scrollPosition = window.pageYOffset;

    // Añadir clases con un pequeño delay para activar las transiciones
    requestAnimationFrame(() => {
      menubar.classList.add("fa-times");
      mynav.classList.add("active");
      body.classList.add("menu-open");
      overlay.classList.add("active");

      // Bloquear scroll y mantener la posición
      body.style.overflow = "hidden";
      body.style.position = "fixed";
      body.style.top = `-${scrollPosition}px`;
      body.style.width = "100%";

      // Prevenir scroll en touch devices
      document.addEventListener("touchmove", preventScroll, { passive: false });
    });
  };

  // FUNCION PARA CERRAR EL MENU
  const closeMenu = () => {
    menubar.classList.remove("fa-times");
    mynav.classList.remove("active");
    body.classList.remove("menu-open");
    overlay.classList.remove("active");

    // RESTAURA EL SCROLL DESPUÉS DE LA TRANSICIÓN
    setTimeout(() => {
      body.style.overflow = "";
      body.style.position = "";
      body.style.top = "";
      body.style.width = "";

      // Restaurar la posición del scroll
      window.scrollTo(0, scrollPosition);

      // QUITA EL SCROLL EN EL TOUCH (CELULARES)
      document.removeEventListener("touchmove", preventScroll);
    }, 300);
  };

  // TOGGLE DEL MENU
  menubar.addEventListener("click", function (e) {
    e.preventDefault();
    e.stopPropagation();

    if (mynav.classList.contains("active")) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  // CERRAR AL HACER CLICK EN EL OVERLAY
  overlay.addEventListener("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    closeMenu();
  });

  // CERRAR EL MENU CUANDO SE HAGA CLICK FUERA DE ÉL
  document.addEventListener("click", function (e) {
    const isClickInsideMenu = mynav.contains(e.target);
    const isClickOnMenuButton = menubar.contains(e.target);

    if (
      !isClickInsideMenu &&
      !isClickOnMenuButton &&
      mynav.classList.contains("active")
    ) {
      closeMenu();
    }
  });

  // PREVENIR QUE AL HACER CLICK DENTRO DEL MENU LO CIERRE
  mynav.addEventListener("click", function (e) {
    e.stopPropagation();
  });

  // CERRAR EL MENU UNA VEZ ELEGIDA LA NUEVA PAGINA
  const navLinks = mynav.querySelectorAll("a");
  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      closeMenu();
    });
  });

  // PODEMOS CERRAR EL MENU CON LA TECLA ESCAPE
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && mynav.classList.contains("active")) {
      closeMenu();
    }
  });

  // SE AÑADE EL PODER CERRAR EL MENU SI SE DESBORDA DE LA PANTALLA
  let resizeTimer;
  window.addEventListener("resize", function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      if (window.innerWidth > 768 && mynav.classList.contains("active")) {
        closeMenu();
      }
    }, 250);
  });
});
// --- Contador de notificaciones (actualiza el badge del header) ---
(function () {
  // Determina base URL definida desde PHP en header (fallback a origin/)
  var BASE_URL =
    window.baseUrl ||
    window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, "/");

  function actualizarContadorHeader() {
    var url =
      BASE_URL + "controllers/NotificacionesAjaxController.php?action=contador";
    fetch(url, { credentials: "same-origin" })
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        var badge = document.getElementById("notification-count");
        if (!badge) return;

        if (data && data.success) {
          // Sumar voluntarios pendientes + expedientes pendientes
          var totalNotificaciones =
            (parseInt(data.totalPendientes) || 0) +
            (parseInt(data.expedientesPendientes) || 0);

          if (totalNotificaciones > 0) {
            badge.textContent = totalNotificaciones;
            badge.style.display = "flex";
            badge.classList.add("show");
          } else {
            badge.textContent = "";
            badge.style.display = "none";
            badge.classList.remove("show");
          }
        } else {
          badge.textContent = "";
          badge.style.display = "none";
          badge.classList.remove("show");
        }
      })
      .catch(function () {
        /* silent error */
      });
  }

  // Permitir que otras partes (ej. n.script.js) actualicen el badge manualmente
  window.actualizarBadgeHeader = function (count) {
    var badge = document.getElementById("notification-count");
    if (!badge) return;
    if (count && parseInt(count) > 0) {
      badge.textContent = parseInt(count);
      badge.style.display = "flex";
    } else {
      badge.textContent = "";
      badge.style.display = "none";
    }
  };

  // Ejecutar al cargar y cada 30s
  document.addEventListener("DOMContentLoaded", function () {
    actualizarContadorHeader();
    setInterval(actualizarContadorHeader, 30000);
  });
})();
