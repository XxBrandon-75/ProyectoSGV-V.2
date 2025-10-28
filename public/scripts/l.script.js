const right = document.getElementById("right");
const registerBtn = document.getElementById("register-btn");

registerBtn.addEventListener("click", () => {
  right.classList.toggle("active");
  registerBtn.textContent = right.classList.contains("active")
    ? "Acceso con cuenta"
    : "Unete al equipo";
});

/* Image rotator for the login left banner */
(function imageRotator() {
  const imgEl = document.querySelector(".left img");
  if (!imgEl) return;

  const images = [
    "public/img/principal.jpg",
    "public/img/segunda.jpg",
    "public/img/tercera.jpeg",
  ];

  const preloaded = images.map((src) => {
    const i = new Image();
    i.src = src;
    return i;
  });

  let index = 0;
  const intervalMs = 5000;

  imgEl.src = images[index];

  setInterval(() => {
    index = (index + 1) % images.length;
    imgEl.classList.add("fade-out");
    setTimeout(() => {
      imgEl.src = images[index];
      imgEl.classList.remove("fade-out");
      imgEl.classList.add("fade-in");
      setTimeout(() => imgEl.classList.remove("fade-in"), 600);
    }, 400);
  }, intervalMs);
})();

/* Details accordion behavior - close others when one opens */
(function accordionBehavior() {
  const allDetails = document.querySelectorAll("details");

  allDetails.forEach((targetDetail) => {
    targetDetail.addEventListener("toggle", () => {
      if (targetDetail.open) {
        allDetails.forEach((detail) => {
          if (detail !== targetDetail && detail.open) {
            detail.open = false;
          }
        });
      }
    });
  });
})();

/* Mostrar/Ocultar datos del tutor según edad */
(function tutorVisibility() {
  const fechaNacimientoInput = document.getElementById("fecha_nacimiento");
  const tutorDetails = document.querySelector("details:has(#tutor_nombre)");
  const tutorInputs = tutorDetails?.querySelectorAll("input, select");

  if (!fechaNacimientoInput || !tutorDetails) return;

  tutorDetails.style.display = "none";
  tutorInputs?.forEach((input) => {
    input.removeAttribute("required");
  });

  function calcularEdad(fechaNacimiento) {
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();

    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
      edad--;
    }

    return edad;
  }

  function toggleTutorSection() {
    const fechaNacimiento = fechaNacimientoInput.value;

    if (!fechaNacimiento) {
      tutorDetails.style.display = "none";
      tutorInputs?.forEach((input) => {
        input.removeAttribute("required");
      });
      return;
    }

    const edad = calcularEdad(fechaNacimiento);

    if (edad < 18) {
      tutorDetails.style.display = "block";
      tutorInputs?.forEach((input) => {
        input.setAttribute("required", "required");
      });
    } else {
      tutorDetails.style.display = "none";
      tutorInputs?.forEach((input) => {
        input.removeAttribute("required");
        input.value = "";
      });
    }

    // Actualizar estado del formulario después de cambiar tutor
    if (window.updateFormState) {
      window.updateFormState();
    }
  }

  fechaNacimientoInput.addEventListener("change", toggleTutorSection);
  fechaNacimientoInput.addEventListener("blur", toggleTutorSection);

  // AGREGAR ESTO: Ejecutar validación al cargar la página si ya hay un valor
  toggleTutorSection();
})();

/* Mostrar/Ocultar campos de vencimiento de licencia y pasaporte */
(function documentosVisibility() {
  window.addEventListener("DOMContentLoaded", function () {
    const licenciaSelect = document.getElementById("licencia_conducir");
    const licenciaVencimientoDiv = document.getElementById(
      "licencia_vencimiento_div"
    );
    const licenciaVencimientoInput = document.getElementById(
      "licencia_vencimiento"
    );

    if (licenciaSelect && licenciaVencimientoDiv && licenciaVencimientoInput) {
      licenciaSelect.addEventListener("change", function () {
        if (this.value === "1") {
          licenciaVencimientoDiv.style.display = "block";
          licenciaVencimientoInput.setAttribute("required", "required");
        } else {
          licenciaVencimientoDiv.style.display = "none";
          licenciaVencimientoInput.removeAttribute("required");
          licenciaVencimientoInput.value = "";
        }
      });
    }

    const pasaporteSelect = document.getElementById("tiene_pasaporte");
    const pasaporteVencimientoDiv = document.getElementById(
      "pasaporte_vencimiento_div"
    );
    const pasaporteVencimientoInput = document.getElementById(
      "pasaporte_vencimiento"
    );

    if (
      pasaporteSelect &&
      pasaporteVencimientoDiv &&
      pasaporteVencimientoInput
    ) {
      pasaporteSelect.addEventListener("change", function () {
        if (this.value === "1") {
          pasaporteVencimientoDiv.style.display = "block";
          pasaporteVencimientoInput.setAttribute("required", "required");
        } else {
          pasaporteVencimientoDiv.style.display = "none";
          pasaporteVencimientoInput.removeAttribute("required");
          pasaporteVencimientoInput.value = "";
        }
      });
    }
  });
})();

/* Sistema de validación progresiva de formulario */
(function progressiveFormValidation() {
  const registerForm = document.querySelector(".register form");
  if (!registerForm) return;

  const allDetails = Array.from(registerForm.querySelectorAll("details"));
  const completedSections = new Array(allDetails.length).fill(false);
  const submitButton = registerForm.querySelector('button[type="submit"]');

  // Deshabilitar botón de submit inicialmente
  if (submitButton) {
    submitButton.disabled = true;
    submitButton.style.opacity = "0.5";
    submitButton.style.cursor = "not-allowed";
  }

  // Función para validar una sección
  function validateSection(detail) {
    const inputs = detail.querySelectorAll(
      "input[required], select[required], textarea[required]"
    );

    // Limpiar estilos de error previos
    const errorMsgs = detail.querySelectorAll(".error-msg");
    errorMsgs.forEach((msg) => {
      if (!msg.id.includes("password")) {
        msg.textContent = "";
      }
    });

    inputs.forEach((input) => {
      input.style.borderColor = "";
      input.style.backgroundColor = "";
    });

    for (let input of inputs) {
      if (input.offsetParent === null || input.disabled) {
        continue;
      }

      let isValid = true;
      let errorMsg = "";

      // Validar contraseña
      if (input.id === "password-registro") {
        const password = input.value;
        if (password.length < 8) {
          isValid = false;
          errorMsg = "La contraseña debe tener mínimo 8 caracteres";
        } else if (!/[A-Z]/.test(password)) {
          isValid = false;
          errorMsg = "Debe contener al menos una mayúscula";
        } else if (!/[0-9]/.test(password)) {
          isValid = false;
          errorMsg = "Debe contener al menos un número";
        } else if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
          isValid = false;
          errorMsg = "Debe contener al menos un símbolo especial";
        }
      }

      // Validar confirmación de contraseña
      if (input.id === "password-confirm") {
        const password = document.getElementById("password-registro").value;
        if (input.value !== password) {
          isValid = false;
          errorMsg = "Las contraseñas no coinciden";
        }
      }

      // Validar campos vacíos
      if (!input.value.trim()) {
        isValid = false;
        errorMsg = "Este campo es obligatorio";
      }

      // Validar select
      if (input.tagName === "SELECT" && (!input.value || input.value === "")) {
        isValid = false;
        errorMsg = "Debes seleccionar una opción";
      }

      // Validar email
      if (input.id === "email-registro") {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(input.value)) {
          isValid = false;
          errorMsg = "Formato de email inválido";
        }
      }

      // Validar CURP
      if (input.id === "curp") {
        const curp = input.value.toUpperCase();
        if (curp.length !== 18) {
          isValid = false;
          errorMsg = `La CURP debe tener exactamente 18 caracteres (tiene ${curp.length})`;
        } else {
          // CAMBIO: últimos 2 caracteres ahora son alfanuméricos (letras o números)
          const curpRegex = /^[A-Z]{4}[0-9]{6}[A-Z]{6}[A-Z0-9]{2}$/;
          if (!curpRegex.test(curp)) {
            isValid = false;
            errorMsg = "Formato de CURP inválido";
          }
        }
      }

      // Validar días disponibles
      if (input.id === "disponibilidad_dia") {
        const diasSeleccionados = parseInt(input.value);
        if (diasSeleccionados === 0 || isNaN(diasSeleccionados)) {
          isValid = false;
          errorMsg = "Debes seleccionar al menos un día disponible";
        }
      }

      if (!isValid) {
        input.style.borderColor = "#e74c3c";
        input.style.borderWidth = "2px";
        input.style.backgroundColor = "#ffe6e6";

        // Mostrar mensaje de error
        const errorElement = input.parentElement.querySelector(".error-msg");
        if (errorElement && errorMsg) {
          errorElement.textContent = errorMsg;
          errorElement.style.color = "#e74c3c";
        }

        input.focus();
        input.scrollIntoView({ behavior: "smooth", block: "center" });
        return false;
      }
    }

    return true;
  }

  // Función para deshabilitar una sección
  function disableSection(detail) {
    detail.style.pointerEvents = "none";
    detail.style.opacity = "0.5";
    detail.style.filter = "grayscale(70%)";
    const summary = detail.querySelector("summary");
    if (summary) {
      summary.style.cursor = "not-allowed";
    }
  }

  // Función para habilitar una sección
  function enableSection(detail) {
    detail.style.pointerEvents = "auto";
    detail.style.opacity = "1";
    detail.style.filter = "none";
    const summary = detail.querySelector("summary");
    if (summary) {
      summary.style.cursor = "pointer";
    }
  }

  // Función para verificar si todas las secciones están completadas
  function checkAllSectionsCompleted() {
    // Recorrer todas las secciones visibles
    for (let i = 0; i < allDetails.length; i++) {
      const detail = allDetails[i];

      // Ignorar secciones ocultas (como tutor)
      if (detail.style.display === "none") {
        completedSections[i] = true;
        continue;
      }

      // Si la sección no está completada, validarla
      if (!completedSections[i]) {
        if (!validateSection(detail)) {
          return false;
        }
      }
    }
    return true;
  }

  // Función para actualizar el estado del botón submit
  function updateSubmitButton() {
    if (!submitButton) return;

    const allCompleted = completedSections.every((completed, index) => {
      // Ignorar secciones ocultas
      if (allDetails[index].style.display === "none") {
        return true;
      }
      return completed;
    });

    if (allCompleted) {
      submitButton.disabled = false;
      submitButton.style.opacity = "1";
      submitButton.style.cursor = "pointer";
    } else {
      submitButton.disabled = true;
      submitButton.style.opacity = "0.5";
      submitButton.style.cursor = "not-allowed";
    }
  }

  // Exportar función para uso externo
  window.updateFormState = updateSubmitButton;

  // Inicializar: solo primera sección habilitada
  allDetails.forEach((detail, index) => {
    if (index === 0) {
      enableSection(detail);
      detail.open = false;
    } else {
      disableSection(detail);
      detail.open = false;
    }
  });

  // Manejar botones "Continuar"
  const continueButtons = registerForm.querySelectorAll(".continue-btn");
  continueButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault();

      const detail = button.closest("details");
      if (!detail) return;

      const currentIndex = allDetails.indexOf(detail);

      // Validar sección actual
      if (validateSection(detail)) {
        // Marcar como completada
        completedSections[currentIndex] = true;

        // Cerrar sección actual
        detail.open = false;

        // Buscar la siguiente sección visible
        let nextIndex = currentIndex + 1;
        while (
          nextIndex < allDetails.length &&
          allDetails[nextIndex].style.display === "none"
        ) {
          completedSections[nextIndex] = true; // Auto-completar secciones ocultas
          nextIndex++;
        }

        // Habilitar y abrir siguiente sección si existe
        if (nextIndex < allDetails.length) {
          enableSection(allDetails[nextIndex]);
          allDetails[nextIndex].open = true;
          allDetails[nextIndex].scrollIntoView({
            behavior: "smooth",
            block: "start",
          });
        }

        // Actualizar estado del botón submit
        updateSubmitButton();

        // Si es la última sección, habilitar el botón de submit
        if (nextIndex >= allDetails.length) {
          // Ya no hay más secciones, habilitar submit
          updateSubmitButton();
        }
      }
    });
  });

  // Limpiar errores al escribir
  const allInputs = registerForm.querySelectorAll("input, select, textarea");
  allInputs.forEach((input) => {
    input.addEventListener("input", () => {
      input.style.borderColor = "";
      input.style.backgroundColor = "";
    });

    if (input.tagName === "SELECT") {
      input.addEventListener("change", () => {
        input.style.borderColor = "";
        input.style.backgroundColor = "";
      });
    }
  });

  // Prevenir envío si no está todo completo
  registerForm.addEventListener("submit", (e) => {
    if (!checkAllSectionsCompleted()) {
      e.preventDefault();
      alert("Por favor completa todas las secciones del formulario.");
    }
  });
})();

/* Validación de contraseñas en tiempo real */
(function passwordValidation() {
  const passwordInput = document.getElementById("password-registro");
  const confirmInput = document.getElementById("password-confirm");
  const passwordError = document.getElementById("password-error");
  const confirmError = document.getElementById("password-confirm-error");

  if (!passwordInput || !confirmInput) return;

  function validatePassword(password) {
    const errors = [];

    if (password.length < 8) {
      errors.push("mínimo 8 caracteres");
    }
    if (!/[A-Z]/.test(password)) {
      errors.push("una mayúscula");
    }
    if (!/[0-9]/.test(password)) {
      errors.push("un número");
    }
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
      errors.push("un símbolo");
    }

    return errors;
  }

  passwordInput.addEventListener("input", () => {
    const password = passwordInput.value;

    if (!password) {
      passwordError.textContent = "";
      passwordInput.classList.remove("invalid", "valid");
      return;
    }

    const errors = validatePassword(password);

    if (errors.length > 0) {
      passwordError.textContent = `Falta: ${errors.join(", ")}`;
      passwordError.style.color = "#e74c3c";
      passwordInput.classList.add("invalid");
      passwordInput.classList.remove("valid");
    } else {
      passwordError.textContent = "";
      passwordInput.classList.remove("invalid", "valid");
    }

    if (confirmInput.value) {
      confirmInput.dispatchEvent(new Event("input"));
    }
  });

  confirmInput.addEventListener("input", () => {
    const password = passwordInput.value;
    const confirm = confirmInput.value;

    if (confirm === "") {
      confirmError.textContent = "";
      confirmInput.classList.remove("invalid", "valid");
      return;
    }

    if (password !== confirm) {
      confirmError.textContent = "Las contraseñas no coinciden";
      confirmError.style.color = "#e74c3c";
      confirmInput.classList.add("invalid");
      confirmInput.classList.remove("valid");
    } else {
      confirmError.textContent = "";
      confirmInput.classList.remove("invalid", "valid");
    }
  });
})();

/* Validación de Email en tiempo real */
(function emailValidation() {
  const emailInput = document.getElementById("email-registro");
  if (!emailInput) return;

  const emailError = document.createElement("small");
  emailError.className = "error-msg";
  emailError.id = "email-error";
  emailInput.parentElement.appendChild(emailError);

  emailInput.addEventListener("input", () => {
    const email = emailInput.value;

    if (!email) {
      emailError.textContent = "";
      emailInput.classList.remove("invalid", "valid");
      return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(email)) {
      emailError.textContent = "Formato de email inválido";
      emailError.style.color = "#e74c3c";
      emailInput.classList.add("invalid");
      emailInput.classList.remove("valid");
    } else {
      emailError.textContent = "";
      emailInput.classList.remove("invalid", "valid");
    }
  });
})();

/* Validación de CURP en tiempo real */
(function curpValidation() {
  const curpInput = document.getElementById("curp");
  if (!curpInput) return;

  const curpError = document.createElement("small");
  curpError.className = "error-msg";
  curpError.id = "curp-error";
  curpInput.parentElement.appendChild(curpError);

  curpInput.addEventListener("input", () => {
    let curp = curpInput.value.toUpperCase();

    // Solo permitir letras y números
    curp = curp.replace(/[^A-Z0-9]/g, "");

    // Convertir automáticamente a mayúsculas
    curpInput.value = curp;

    if (!curp) {
      curpError.textContent = "";
      curpInput.classList.remove("invalid", "valid");
      return;
    }

    if (curp.length !== 18) {
      curpError.textContent = `Faltan ${
        18 - curp.length
      } caracteres (debe tener 18)`;
      curpError.style.color = "#e74c3c";
      curpInput.classList.add("invalid");
      curpInput.classList.remove("valid");
    } else {
      // CAMBIO: Validar formato CURP con últimos 2 caracteres alfanuméricos
      // 4 letras, 6 números, 6 letras, 2 letras/números
      const curpRegex = /^[A-Z]{4}[0-9]{6}[A-Z]{6}[A-Z0-9]{2}$/;

      if (!curpRegex.test(curp)) {
        curpError.textContent =
          "Formato de CURP inválido (4 letras, 6 números, 6 letras, 2 letras/números)";
        curpError.style.color = "#e74c3c";
        curpInput.classList.add("invalid");
        curpInput.classList.remove("valid");
      } else {
        curpError.textContent = "";
        curpInput.classList.remove("invalid", "valid");
      }
    }
  });
})();

/* Contador de días disponibles */
(function diasDisponiblesCounter() {
  const checkboxes = document.querySelectorAll(
    '.dias-disponibles input[type="checkbox"]'
  );
  const hiddenInput = document.getElementById("disponibilidad_dia");
  const counter = document.querySelector(".dias-counter strong");

  if (!checkboxes.length || !hiddenInput || !counter) return;

  function updateCounter() {
    const checked = Array.from(checkboxes).filter((cb) => cb.checked);
    const count = checked.length;

    counter.textContent = count;
    hiddenInput.value = count;

    // Validar que al menos 1 día esté seleccionado
    if (count > 0) {
      hiddenInput.setCustomValidity("");
    } else {
      hiddenInput.setCustomValidity("Debes seleccionar al menos un día");
    }
  }

  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", updateCounter);
  });

  // Inicializar
  updateCounter();
})();

/* Limpiar mensajes de error al escribir */
(function clearErrorMessages() {
  const registerForm = document.querySelector(".register form");
  if (!registerForm) return;

  const errorMessageDiv = document.querySelector(".error-message");
  const allInputs = registerForm.querySelectorAll("input, select, textarea");

  if (!errorMessageDiv) return;

  // Cuando el usuario empiece a escribir, ocultar el mensaje de error
  allInputs.forEach((input) => {
    input.addEventListener("input", () => {
      errorMessageDiv.style.display = "none";

      // Limpiar datos guardados en sesión mediante AJAX
      fetch("login.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "clear_form_data=1",
      });
    });

    input.addEventListener("change", () => {
      errorMessageDiv.style.display = "none";
    });
  });
})();
