<?php
require_once __DIR__ . '/../../helpers/RolHelper.php';
require_once __DIR__ . '/../../helpers/SecurityHelper.php';

// Generar token CSRF para protección de formularios
$csrfToken = SecurityHelper::generarTokenCSRF();

// Función helper para mostrar valores con fallback a "No especificado"
function mostrarValor($valor, $default = 'No especificado')
{
  if ($valor === null || $valor === '' || (is_string($valor) && trim($valor) === '')) {
    return $default;
  }
  return htmlspecialchars($valor);
}

// Determinar si el usuario puede editar roles y ver coordinadores
$puedeEditarRoles = RolHelper::puedeEditarRoles();
$puedeVerCoordinadores = RolHelper::puedeVerCoordinadores();

// Obtener campos editables según el rol
// $esPropioUsuario y $puedeModificar vienen del controlador
$camposEditables = $puedeModificar ? RolHelper::obtenerCamposEditables($esPropioUsuario) : [];

// Campos que NUNCA se editan (solo lectura)
$camposSoloLectura = [
  'VoluntarioID',
  'FechaRegistro',
  'LugarNacimientoID',
  'Nacionalidad'
];

$puedeEditarPersonalCompleto = $puedeEditarRoles;
$tieneAccesoPersonal = $puedeModificar;
$puedeEditarProfesional = $puedeModificar;
$puedeEditarMedica = $puedeModificar;
$puedeEditarVoluntariado = $puedeEditarRoles;
$puedeEditarContacto = $puedeModificar;
$puedeEditarEmergencia = $puedeModificar;
$puedeEditarDireccion = $puedeModificar;
$puedeEditarTutor = $puedeEditarRoles;
?>

<?php
// Definir página actual para el menú
$paginaActual = 'perfil';
// Incluir menú lateral
require_once __DIR__ . '/../layout/perfil-menu.php';
?>

<section class="perfil-contenido" id="perfil-contenido">

  <!-- SECCIÓN: MI PERFIL -->
  <div class="seccion-contenido activa" id="seccion-mi-perfil">
    <h2><?php echo $esPropioUsuario ? 'Mi Perfil' : 'Perfil de ' . htmlspecialchars($datosUsuario['Nombres']); ?></h2>

    <?php
    // Mostrar alertas de aprobación si el voluntario está pendiente y el usuario tiene permisos
    // Usar RolHelper para verificar permisos (coordinador o superior)
    $esCoordinadorOMas = RolHelper::puedeVerCoordinadores(); // Coordinador, Admin o Superadmin
    $voluntarioPendiente = isset($datosUsuario['EstatusNombre']) && $datosUsuario['EstatusNombre'] === 'Pendiente';

    if (!$esPropioUsuario && $esCoordinadorOMas && $voluntarioPendiente): ?>
      <!-- Alerta de voluntario pendiente de aprobación -->
      <div class="alerta-aprobacion">
        <div class="alerta-header">
          <i class="fa-solid fa-exclamation-triangle"></i>
          <h3>Este voluntario está pendiente de aprobación</h3>
        </div>
        <p>Este voluntario requiere tu aprobación para poder acceder al sistema y participar activamente en la red de voluntarios.</p>
        <div class="alerta-acciones">
          <button class="btn-accion btn-aprobar" onclick="aprobarVoluntarioDesdeModal(<?= $datosUsuario['VoluntarioID'] ?>)">
            <i class="fa-solid fa-check"></i> Aprobar Voluntario
          </button>
          <button class="btn-accion btn-rechazar" onclick="rechazarVoluntarioDesdeModal(<?= $datosUsuario['VoluntarioID'] ?>)">
            <i class="fa-solid fa-times"></i> Rechazar Voluntario
          </button>
        </div>
      </div>
    <?php endif; ?>

    <!-- Información Personal -->
    <div class="perfil-card">
      <div class="card-header">
        <h3><i class="fa-solid fa-user"></i> Información Personal</h3>
        <div style="display: flex; gap: 10px;">
          <?php if ($puedeEditarPersonalCompleto): ?>
            <!-- Permisos completos (Superadmin/Admin): Solo botón Editar -->
            <button class="btn-editar" onclick="editarSeccion('personal', event)">
              <i class="fa-solid fa-pen"></i> Editar
            </button>
          <?php elseif ($tieneAccesoPersonal && $esPropioUsuario): ?>
            <!-- Permisos parciales en su propio perfil: Editar + Solicitar -->
            <button class="btn-editar" onclick="editarSeccion('personal', event)">
              <i class="fa-solid fa-pen"></i> Editar
            </button>
            <!-- <button class="btn-solicitar" onclick="solicitarActualizacion('personal')">
              <i class="fa-solid fa-paper-plane"></i> Solicitar actualización
            </button> -->
          <?php elseif ($esPropioUsuario): ?>
            <!-- Sin permisos de edición en su propio perfil: Solo Solicitar -->
            <!-- <button class="btn-solicitar" onclick="solicitarActualizacion('personal')">
              <i class="fa-solid fa-paper-plane"></i> Solicitar actualización
            </button> -->
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div class="info-item">
            <label>Nombre completo:</label>
            <span><?php echo mostrarValor(trim(($datosUsuario['Nombres'] ?? '') . ' ' . ($datosUsuario['ApellidoPaterno'] ?? '') . ' ' . ($datosUsuario['ApellidoMaterno'] ?? ''))); ?></span>
          </div>
          <div class="info-item">
            <label>CURP:</label>
            <span><?php echo mostrarValor($datosUsuario['CURP'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Fecha de nacimiento:</label>
            <span><?php echo isset($datosUsuario['FechaNacimiento']) && $datosUsuario['FechaNacimiento'] ? date('d/m/Y', strtotime($datosUsuario['FechaNacimiento'])) : 'No especificado'; ?></span>
          </div>
          <div class="info-item">
            <label>Sexo:</label>
            <span><?php echo isset($datosUsuario['Sexo']) && $datosUsuario['Sexo'] ? ($datosUsuario['Sexo'] == 'M' ? 'Masculino' : 'Femenino') : 'No especificado'; ?></span>
          </div>
          <div class="info-item">
            <label>Estado civil:</label>
            <span><?php echo mostrarValor($datosUsuario['EstadoCivilNombre'] ?? ''); ?></span>
          </div>
        </div>
      </div>
    </div>

    <?php
    // Mostrar información del tutor si es menor de edad
    // Se considera menor si tiene TutorID asignado (no NULL)
    if (!empty($datosUsuario['TutorID'])):
    ?>
      <!-- Información del Tutor (Menores de edad) -->
      <div class="perfil-card">
        <div class="card-header">
          <h3><i class="fa-solid fa-user-shield"></i> Información del Tutor</h3>
          <?php if ($puedeEditarTutor): ?>
            <button class="btn-editar" onclick="editarSeccion('tutor', event)">
              <i class="fa-solid fa-pen"></i> Editar
            </button>
          <?php elseif ($esPropioUsuario): ?>
            <!-- <button class="btn-solicitar" onclick="solicitarActualizacion('tutor')">
              <i class="fa-solid fa-paper-plane"></i> Solicitar actualización
            </button> -->
          <?php endif; ?>
        </div>
        <div class="card-body">
          <div class="info-grid">
            <div class="info-item">
              <label>Nombre completo del tutor:</label>
              <span><?php echo mostrarValor($datosUsuario['TutorNombreCompleto'] ?? ''); ?></span>
            </div>
            <div class="info-item">
              <label>Parentesco:</label>
              <span><?php echo mostrarValor($datosUsuario['TutorParentesco'] ?? ''); ?></span>
            </div>
            <div class="info-item">
              <label>Teléfono del tutor:</label>
              <span><?php echo mostrarValor($datosUsuario['TutorTelefono'] ?? ''); ?></span>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Información de Contacto -->
    <div class="perfil-card">
      <div class="card-header">
        <h3><i class="fa-solid fa-phone"></i> Información de Contacto</h3>
        <?php if ($puedeEditarContacto): ?>
          <button class="btn-editar" onclick="editarSeccion('contacto', event)">
            <i class="fa-solid fa-pen"></i> Editar
          </button>
        <?php elseif ($esPropioUsuario): ?>
          <!-- <button class="btn-solicitar" onclick="solicitarActualizacion('contacto')">
            <i class="fa-solid fa-paper-plane"></i> Solicitar actualización
          </button> -->
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div class="info-item">
            <label>Email:</label>
            <span><?php echo mostrarValor($datosUsuario['Email'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Teléfono celular:</label>
            <span><?php echo mostrarValor($datosUsuario['TelefonoCelular'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Teléfono particular:</label>
            <span><?php echo mostrarValor($datosUsuario['TelefonoParticular'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Teléfono de trabajo:</label>
            <span><?php echo mostrarValor($datosUsuario['TelefonoTrabajo'] ?? ''); ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Contacto de Emergencia -->
    <div class="perfil-card">
      <div class="card-header">
        <h3><i class="fa-solid fa-hospital"></i> Contacto de Emergencia</h3>
        <?php if ($puedeEditarEmergencia): ?>
          <button class="btn-editar" onclick="editarSeccion('emergencia', event)">
            <i class="fa-solid fa-pen"></i> Editar
          </button>
        <?php elseif ($esPropioUsuario): ?>
          <!-- <button class="btn-solicitar" onclick="solicitarActualizacion('emergencia')">
            <i class="fa-solid fa-paper-plane"></i> Solicitar actualización
          </button> -->
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div class="info-item">
            <label>Nombre del contacto:</label>
            <span><?php echo mostrarValor($datosUsuario['ContactoEmergenciaNombre'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Parentesco:</label>
            <span><?php echo mostrarValor($datosUsuario['ContactoEmergenciaParentesco'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Teléfono:</label>
            <span><?php echo mostrarValor($datosUsuario['ContactoEmergenciaTelefono'] ?? ''); ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Dirección -->
    <div class="perfil-card">
      <div class="card-header">
        <h3><i class="fa-solid fa-map-marker-alt"></i> Dirección</h3>
        <?php if ($puedeEditarDireccion): ?>
          <button class="btn-editar" onclick="editarSeccion('direccion', event)">
            <i class="fa-solid fa-pen"></i> Editar
          </button>
        <?php elseif ($esPropioUsuario): ?>
          <!-- <button class="btn-solicitar" onclick="solicitarActualizacion('direccion')">
            <i class="fa-solid fa-paper-plane"></i> Solicitar actualización
          </button> -->
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div class="info-item">
            <label>Calle:</label>
            <span><?php echo mostrarValor($datosUsuario['Calle'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Número exterior:</label>
            <span><?php echo mostrarValor($datosUsuario['NumeroExterior'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Número interior:</label>
            <span><?php echo mostrarValor($datosUsuario['NumeroInterior'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Colonia:</label>
            <span><?php echo mostrarValor($datosUsuario['Colonia'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Código postal:</label>
            <span><?php echo mostrarValor($datosUsuario['CodigoPostal'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Estado:</label>
            <span><?php echo mostrarValor($datosUsuario['EstadoNombre'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Ciudad:</label>
            <span><?php echo mostrarValor($datosUsuario['CiudadNombre'] ?? ''); ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Información Profesional -->
    <div class="perfil-card">
      <div class="card-header">
        <h3><i class="fa-solid fa-briefcase"></i> Información Profesional</h3>
        <?php if ($puedeEditarProfesional): ?>
          <button class="btn-editar" onclick="editarSeccion('profesional', event)">
            <i class="fa-solid fa-pen"></i> Editar
          </button>
        <?php elseif ($esPropioUsuario): ?>
          <!-- <button class="btn-solicitar" onclick="solicitarActualizacion('profesional')">
            <i class="fa-solid fa-paper-plane"></i> Solicitar actualización
          </button> -->
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div class="info-item">
            <label>Grado de estudios:</label>
            <span><?php echo mostrarValor($datosUsuario['GradoEstudios'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Profesión:</label>
            <span><?php echo mostrarValor($datosUsuario['Profesion'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Ocupación actual:</label>
            <span><?php echo mostrarValor($datosUsuario['OcupacionActual'] ?? ''); ?></span>
          </div>
          <div class="info-item">
            <label>Empresa:</label>
            <span><?php echo mostrarValor($datosUsuario['EmpresaLabora'] ?? ''); ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Información Médica -->
    <div class="perfil-card">
      <div class="card-header">
        <h3><i class="fa-solid fa-notes-medical"></i> Información Médica</h3>
        <?php if ($puedeEditarMedica): ?>
          <button class="btn-editar" onclick="editarSeccion('medica', event)">
            <i class="fa-solid fa-pen"></i> Editar
          </button>
        <?php elseif ($esPropioUsuario): ?>
          <!-- <button class="btn-solicitar" onclick="solicitarActualizacion('medica')">
            <i class="fa-solid fa-paper-plane"></i> Solicitar actualización
          </button> -->
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div class="info-item full-width">
            <label>Grupo Sanguineo:</label>
            <span><?php echo mostrarValor($datosUsuario['GrupoSanguineoNombre'] ?? '', 'Ninguno'); ?></span>
          </div>
          <div class="info-item full-width">
            <label>Enfermedades:</label>
            <span><?php echo mostrarValor($datosUsuario['Enfermedades'] ?? '', 'Ninguna'); ?></span>
          </div>
          <div class="info-item full-width">
            <label>Alergias:</label>
            <span><?php echo mostrarValor($datosUsuario['Alergias'] ?? '', 'Ninguna'); ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Información de Voluntariado -->
    <div class="perfil-card">
      <div class="card-header">
        <h3><i class="fa-solid fa-hands-helping"></i> Información de Voluntariado</h3>
        <?php if ($puedeEditarVoluntariado): ?>
          <button class="btn-editar" onclick="editarSeccion('voluntariado', event)">
            <i class="fa-solid fa-pen"></i> Editar
          </button>
        <?php elseif ($esPropioUsuario): ?>
          <!-- <button class="btn-solicitar" onclick="solicitarActualizacion('voluntariado')">
            <i class="fa-solid fa-paper-plane"></i> Solicitar actualización
          </button> -->
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div class="info-item">
            <label>Área asignada:</label>
            <span><?php echo htmlspecialchars($datosUsuario['AreaNombre'] ?? 'No asignado'); ?></span>
          </div>
          <div class="info-item">
            <label>Delegación:</label>
            <span><?php echo htmlspecialchars($datosUsuario['DelegacionNombre'] ?? 'No asignado'); ?></span>
          </div>
          <div class="info-item">
            <label>Rol:</label>
            <span class="badge-rol"><?php echo htmlspecialchars($datosUsuario['RolNombre'] ?? 'Voluntario'); ?></span>
          </div>
          <div class="info-item">
            <label>Fecha de registro:</label>
            <span><?php echo isset($datosUsuario['FechaRegistro']) ? date('d/m/Y', strtotime($datosUsuario['FechaRegistro'])) : 'No especificado'; ?></span>
          </div>
        </div>
      </div>
    </div>

    <?php if ($puedeEditarRoles && !$esPropioUsuario): ?>
      <div class="baja-voluntario-container">
        <form action="<?php echo $base_url; ?>index.php?controller=home&action=bajaVoluntario" method="post" onsubmit="return confirm('¿Estás seguro de que deseas dar de baja a este voluntario? Esta acción no se puede deshacer.');">
          <input type="hidden" name="voluntario_id" value="<?php echo htmlspecialchars($datosUsuario['VoluntarioID']); ?>">
          <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
          <button type="submit" class="btn-baja" <?php echo (isset($datosUsuario['EstatusNombre']) && $datosUsuario['EstatusNombre'] !== 'Activo') ? 'disabled style="opacity:0.6;cursor:not-allowed;"' : ''; ?>>
            <i class="fa-solid fa-user-slash"></i> Dar de baja voluntario
          </button>
        </form>
      </div>
    <?php endif; ?>
  </div>

</section>

<!-- Modal para editar información -->
<div id="modal-editar" class="modal">
  <div class="modal-content">
    <span class="cerrar-modal" onclick="cerrarModal()">&times;</span>
    <h2 id="modal-titulo">Editar información</h2>
    <form id="form-editar" onsubmit="guardarCambios(event)">
      <!-- Token CSRF para protección contra ataques CSRF -->
      <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
      <div id="campos-editar"></div>
      <div class="modal-botones">
        <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
        <button type="submit" class="btn-guardar">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<script>
  <?php
  $voluntarioModel = new Voluntario();
  $datosUsuarioLogueado = $voluntarioModel->obtenerDatosCompletos($_SESSION['user']['id']);
  $rolIDLogueado = (int)$datosUsuarioLogueado['RolID'];

  // SEGURIDAD: Filtrar datos sensibles antes de exponerlos en JavaScript
  // Solo incluir campos necesarios para la funcionalidad del frontend
  $datosSegurosParaJS = [
    'VoluntarioID' => $datosUsuario['VoluntarioID'] ?? null,
    'Nombres' => $datosUsuario['Nombres'] ?? '',
    'ApellidoPaterno' => $datosUsuario['ApellidoPaterno'] ?? '',
    'ApellidoMaterno' => $datosUsuario['ApellidoMaterno'] ?? '',
    'CURP' => $datosUsuario['CURP'] ?? '',
    'Email' => $datosUsuario['Email'] ?? '',
    'TelefonoCelular' => $datosUsuario['TelefonoCelular'] ?? '',
    'TelefonoParticular' => $datosUsuario['TelefonoParticular'] ?? '',
    'TelefonoTrabajo' => $datosUsuario['TelefonoTrabajo'] ?? '',
    'FechaNacimiento' => $datosUsuario['FechaNacimiento'] ?? '',
    'Sexo' => $datosUsuario['Sexo'] ?? '',
    'EstadoCivilID' => $datosUsuario['EstadoCivilID'] ?? null,
    'EstadoCivilNombre' => $datosUsuario['EstadoCivilNombre'] ?? '',
    'GrupoSanguineoID' => $datosUsuario['GrupoSanguineoID'] ?? null,
    'GrupoSanguineoNombre' => $datosUsuario['GrupoSanguineoNombre'] ?? '',
    'GradoEstudios' => $datosUsuario['GradoEstudios'] ?? '',
    'Profesion' => $datosUsuario['Profesion'] ?? '',
    'OcupacionActual' => $datosUsuario['OcupacionActual'] ?? '',
    'EmpresaLabora' => $datosUsuario['EmpresaLabora'] ?? '',
    'Enfermedades' => $datosUsuario['Enfermedades'] ?? '',
    'Alergias' => $datosUsuario['Alergias'] ?? '',
    'Calle' => $datosUsuario['Calle'] ?? '',
    'NumeroExterior' => $datosUsuario['NumeroExterior'] ?? '',
    'NumeroInterior' => $datosUsuario['NumeroInterior'] ?? '',
    'Colonia' => $datosUsuario['Colonia'] ?? '',
    'CodigoPostal' => $datosUsuario['CodigoPostal'] ?? '',
    'CiudadID' => $datosUsuario['CiudadID'] ?? null,
    'CiudadNombre' => $datosUsuario['CiudadNombre'] ?? '',
    'EstadoID' => $datosUsuario['EstadoID'] ?? null,
    'EstadoNombre' => $datosUsuario['EstadoNombre'] ?? '',
    'ContactoEmergenciaNombre' => $datosUsuario['ContactoEmergenciaNombre'] ?? '',
    'ContactoEmergenciaParentesco' => $datosUsuario['ContactoEmergenciaParentesco'] ?? '',
    'ContactoEmergenciaTelefono' => $datosUsuario['ContactoEmergenciaTelefono'] ?? '',
    'TutorID' => $datosUsuario['TutorID'] ?? null,
    'TutorNombreCompleto' => $datosUsuario['TutorNombreCompleto'] ?? '',
    'TutorParentesco' => $datosUsuario['TutorParentesco'] ?? '',
    'TutorTelefono' => $datosUsuario['TutorTelefono'] ?? '',
    'AreaID' => $datosUsuario['AreaID'] ?? null,
    'DelegacionID' => $datosUsuario['DelegacionID'] ?? null,
    'RolID' => $datosUsuario['RolID'] ?? null,
    'EstatusID' => $datosUsuario['EstatusID'] ?? null
  ];
  // NOTA: CURP incluido para permitir edición por administradores
  // NUNCA incluir: PasswordHash, información bancaria, etc.
  ?>
  // Configuración global del perfil
  window.perfilConfig = {
    camposEditables: <?php echo json_encode($camposEditables); ?>,
    puedeModificar: <?php echo $puedeModificar ? 'true' : 'false'; ?>,
    esAdmin: <?php echo $puedeEditarRoles ? 'true' : 'false'; ?>,
    esPropioUsuario: <?php echo $esPropioUsuario ? 'true' : 'false'; ?>,
    datosUsuario: <?php echo json_encode($datosSegurosParaJS); ?>,
    rolUsuarioActual: <?php echo $rolIDLogueado; ?>,
    idUsuarioActual: <?php echo (int)$_SESSION['user']['id']; ?>,
    catCiudades: <?php echo json_encode($catCiudades); ?>,
    catEstados: <?php echo json_encode($catEstados); ?>
  };
</script>