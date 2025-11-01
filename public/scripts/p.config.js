// Configuración de secciones para el modal de edición
// Este archivo define la estructura de cada sección del perfil

const seccionesConfig = {
  contacto: {
    titulo: "Editar Información de Contacto",
    campos: [
      {
        nombre: "Email",
        label: "Correo electrónico",
        tipo: "email",
        required: true,
        pattern: "[a-z0-9._%+\\-]+@[a-z0-9.\\-]+\\.[a-z]{2,}",
        title: "Ingresa un correo electrónico válido",
      },
      {
        nombre: "TelefonoCelular",
        label: "Teléfono celular",
        tipo: "tel",
        required: true,
        pattern: "[0-9]{10}",
        maxlength: 10,
        title: "Debe contener exactamente 10 dígitos",
      },
      {
        nombre: "TelefonoParticular",
        label: "Teléfono particular",
        tipo: "tel",
        pattern: "[0-9]{10}",
        maxlength: 10,
        title: "Debe contener exactamente 10 dígitos",
      },
      {
        nombre: "TelefonoTrabajo",
        label: "Teléfono de trabajo",
        tipo: "tel",
        pattern: "[0-9]{10}",
        maxlength: 10,
        title: "Debe contener exactamente 10 dígitos",
      },
    ],
  },

  direccion: {
    titulo: "Editar Dirección",
    campos: [
      {
        nombre: "Calle",
        label: "Calle",
        tipo: "text",
        required: true,
        maxlength: 100,
      },
      {
        nombre: "NumeroExterior",
        label: "Número exterior",
        tipo: "text",
        maxlength: 10,
      },
      {
        nombre: "NumeroInterior",
        label: "Número interior",
        tipo: "text",
        maxlength: 10,
      },
      {
        nombre: "Colonia",
        label: "Colonia",
        tipo: "text",
        required: true,
        maxlength: 100,
      },
      {
        nombre: "CodigoPostal",
        label: "Código postal",
        tipo: "text",
        required: true,
        pattern: "[0-9]{5}",
        maxlength: 5,
        title: "Debe contener exactamente 5 dígitos",
      },
      {
        nombre: "EstadoID",
        label: "Estado",
        tipo: "select",
        required: true,
        catalogoSource: "catEstados",
        valueField: "EstadoID",
        labelField: "Nombre",
      },
      {
        nombre: "CiudadID",
        label: "Ciudad",
        tipo: "select",
        required: true,
        catalogoSource: "catCiudades",
        valueField: "CiudadID",
        labelField: "Nombre",
      },
    ],
  },

  emergencia: {
    titulo: "Editar Contacto de Emergencia",
    campos: [
      {
        nombre: "ContactoEmergenciaNombre",
        label: "Nombre del contacto",
        tipo: "text",
        required: true,
        maxlength: 100,
      },
      {
        nombre: "ContactoEmergenciaParentesco",
        label: "Parentesco",
        tipo: "text",
        required: true,
        maxlength: 50,
      },
      {
        nombre: "ContactoEmergenciaTelefono",
        label: "Teléfono",
        tipo: "tel",
        required: true,
        pattern: "[0-9]{10}",
        maxlength: 10,
        title: "Debe contener exactamente 10 dígitos",
      },
    ],
  },

  profesional: {
    titulo: "Editar Información Profesional",
    campos: [
      {
        nombre: "GradoEstudios",
        label: "Grado de estudios",
        tipo: "text",
        required: true,
        maxlength: 50,
      },
      {
        nombre: "Profesion",
        label: "Profesión",
        tipo: "text",
        required: true,
        maxlength: 100,
      },
      {
        nombre: "OcupacionActual",
        label: "Ocupación actual",
        tipo: "text",
        required: true,
        maxlength: 100,
      },
      {
        nombre: "EmpresaLabora",
        label: "Empresa donde labora",
        tipo: "text",
        maxlength: 100,
      },
    ],
  },

  medica: {
    titulo: "Editar Información Médica",
    campos: [
      {
        nombre: "GrupoSanguineoID",
        label: "Grupo sanguíneo",
        tipo: "select",
        required: true,
        catalogoSource: "catalogosDB",
        catalogoKey: "gruposSanguineos",
        valueField: "GrupoSanguineoID",
        labelField: "Nombre",
      },
      {
        nombre: "Enfermedades",
        label: "Enfermedades",
        tipo: "textarea",
        maxlength: 500,
        placeholder: "Especifica si padeces alguna enfermedad",
      },
      {
        nombre: "Alergias",
        label: "Alergias",
        tipo: "textarea",
        maxlength: 500,
        placeholder: "Especifica si tienes alguna alergia",
      },
    ],
  },

  tutor: {
    titulo: "Editar Información del Tutor",
    campos: [
      {
        nombre: "TutorNombreCompleto",
        label: "Nombre completo del tutor",
        tipo: "text",
        required: true,
        maxlength: 200,
      },
      {
        nombre: "TutorParentesco",
        label: "Parentesco",
        tipo: "text",
        required: true,
        maxlength: 50,
      },
      {
        nombre: "TutorTelefono",
        label: "Teléfono del tutor",
        tipo: "tel",
        required: true,
        pattern: "[0-9]{10}",
        maxlength: 10,
        title: "Debe contener exactamente 10 dígitos",
      },
    ],
  },

  voluntariado: {
    titulo: "Editar Información de Voluntariado",
    campos: [
      {
        nombre: "AreaID",
        label: "Área",
        tipo: "select",
        catalogoSource: "catalogosDB",
        catalogoKey: "areas",
        valueField: "AreaID",
        labelField: "Nombre",
      },
      {
        nombre: "DelegacionID",
        label: "Delegación",
        tipo: "select",
        catalogoSource: "catalogosDB",
        catalogoKey: "delegaciones",
        valueField: "DelegacionID",
        labelField: "Nombre",
      },
      {
        nombre: "RolID",
        label: "Rol",
        tipo: "select",
        catalogoSource: "catalogosDB",
        catalogoKey: "roles",
        valueField: "RolID",
        labelField: "Nombre",
        filtroOpciones: (opciones) => {
          // Si el usuario está editando su propio perfil, no puede cambiar su rol
          if (window.perfilConfig?.esPropioUsuario) {
            return [];
          }

          const rolUsuarioActual = window.perfilConfig?.rolUsuarioActual;

          // Superadministrador (4) puede asignar cualquier rol
          if (rolUsuarioActual === 4) {
            return opciones;
          }

          // Administrador (3) puede asignar todos los roles EXCEPTO Superadministrador
          if (rolUsuarioActual === 3) {
            return opciones.filter((rol) => parseInt(rol.value) < 4);
          }

          // Coordinador (2) y Voluntario (1) no pueden cambiar roles
          return [];
        },
      },
    ],
  },

  personal: {
    titulo: "Editar Información Personal",
    campos: [
      {
        nombre: "Nombres",
        label: "Nombre(s)",
        tipo: "text",
        required: true,
        maxlength: 100,
        pattern: "[A-Za-zÁÉÍÓÚáéíóúÑñ\\s]+",
        title: "Solo se permiten letras y espacios",
      },
      {
        nombre: "ApellidoPaterno",
        label: "Apellido paterno",
        tipo: "text",
        required: true,
        maxlength: 50,
        pattern: "[A-Za-zÁÉÍÓÚáéíóúÑñ\\s]+",
        title: "Solo se permiten letras y espacios",
      },
      {
        nombre: "ApellidoMaterno",
        label: "Apellido materno",
        tipo: "text",
        required: true,
        maxlength: 50,
        pattern: "[A-Za-zÁÉÍÓÚáéíóúÑñ\\s]+",
        title: "Solo se permiten letras y espacios",
      },
      {
        nombre: "CURP",
        label: "CURP",
        tipo: "text",
        required: true,
        pattern: "[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}",
        minlength: 18,
        maxlength: 18,
        title: "Debe ser un CURP válido de 18 caracteres",
      },
      {
        nombre: "FechaNacimiento",
        label: "Fecha de nacimiento",
        tipo: "date",
        required: true,
      },
      {
        nombre: "Sexo",
        label: "Sexo",
        tipo: "select",
        required: true,
        opcionesEstaticas: [
          { value: "M", label: "Masculino" },
          { value: "F", label: "Femenino" },
        ],
      },
      {
        nombre: "EstadoCivilID",
        label: "Estado civil",
        tipo: "select",
        required: true,
        catalogoSource: "catalogosDB",
        catalogoKey: "estadosCiviles",
        valueField: "EstadoCivilID",
        labelField: "Nombre",
      },
    ],
  },

  foto_perfil: {
    titulo: "Cambiar foto de perfil",
    campos: [
      {
        nombre: "foto_perfil",
        label: "Nueva foto de perfil",
        tipo: "file",
        required: true,
        accept: "image/*",
        title: "Selecciona una imagen (JPG, PNG, WEBP, máx. 2MB)",
      },
    ],
  },
};
