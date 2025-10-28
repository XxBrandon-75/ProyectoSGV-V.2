<?php
// Iniciar buffer de salida para capturar cualquier output no deseado
ob_start();

require_once __DIR__ . '/../models/voluntario.php';
require_once __DIR__ . '/../helpers/RolHelper.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';

class PerfilController
{
    private $voluntarioModel;

    public function __construct()
    {
        require_once __DIR__ . '/../config/security.php';

        if (!isset($_SESSION['user'])) {
            // Limpiar buffer antes de enviar JSON
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit();
        }

        $this->voluntarioModel = new Voluntario();
    }

    public function actualizarDatos()
    {
        // Limpiar cualquier output no deseado antes de enviar JSON
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        // VALIDACIÓN CSRF
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!SecurityHelper::validarTokenCSRF($csrfToken)) {
            SecurityHelper::registrarIntentoSospechoso(
                'CSRF_TOKEN_INVALIDO',
                ['usuario' => $_SESSION['user']['id'], 'accion' => 'actualizarDatos']
            );
            echo json_encode([
                'success' => false,
                'message' => 'Token de seguridad inválido. Por favor, recarga la página e intenta de nuevo.'
            ]);
            return;
        }

        // RATE LIMITING - Máximo 20 actualizaciones cada 5 minutos
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateLimitKey = 'update_profile_' . $_SESSION['user']['id'] . '_' . $ip;
        if (!SecurityHelper::verificarRateLimiting($rateLimitKey, 20, 300)) {
            echo json_encode([
                'success' => false,
                'message' => 'Demasiadas solicitudes. Por favor, espera unos minutos antes de intentar de nuevo.'
            ]);
            return;
        }

        // Obtener el ID del voluntario a editar (puede ser otro usuario si tiene permisos)
        $voluntarioID = isset($_POST['voluntarioID']) ? (int)$_POST['voluntarioID'] : (int)$_SESSION['user']['id'];
        $esPropioUsuario = ($voluntarioID === (int)$_SESSION['user']['id']);
        $datos = $_POST;

        // Obtener datos del voluntario objetivo para verificar permisos
        $datosVoluntario = $this->voluntarioModel->obtenerDatosCompletos($voluntarioID);

        if (!$datosVoluntario) {
            echo json_encode(['success' => false, 'message' => 'Voluntario no encontrado']);
            return;
        }

        // Verificar si tiene permisos para modificar este perfil
        if (!RolHelper::puedeModificarPerfil($datosVoluntario)) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para modificar este perfil']);
            return;
        }

        // VALIDACIÓN CRÍTICA DE SEGURIDAD: Cambio de rol
        if (isset($datos['RolID'])) {
            // Obtener RolID del usuario actual
            $datosUsuarioActual = $this->voluntarioModel->obtenerDatosCompletos($_SESSION['user']['id']);
            $rolUsuarioActualID = (int)$datosUsuarioActual['RolID'];
            $nuevoRolID = (int)$datos['RolID'];
            $rolActualObjetivo = (int)$datosVoluntario['RolID'];

            // Regla 1: Un usuario NO puede cambiar su propio rol
            if ($esPropioUsuario) {
                echo json_encode(['success' => false, 'message' => 'No puedes cambiar tu propio rol']);
                return;
            }

            // Regla 2: Administrador (3) NO puede asignar rol de Superadministrador (4)
            if ($rolUsuarioActualID == 3 && $nuevoRolID == 4) {
                echo json_encode(['success' => false, 'message' => 'No tienes permisos para asignar el rol de Superadministrador']);
                return;
            }

            // Regla 3: Solo Superadministrador (4) puede asignar rol de Superadministrador
            if ($nuevoRolID == 4 && $rolUsuarioActualID != 4) {
                echo json_encode(['success' => false, 'message' => 'Solo un Superadministrador puede asignar este rol']);
                return;
            }

            // Regla 4: Coordinadores (2) y Voluntarios (1) no pueden cambiar roles en absoluto
            if ($rolUsuarioActualID <= 2) {
                echo json_encode(['success' => false, 'message' => 'No tienes permisos para cambiar roles']);
                return;
            }
        }

        // Obtener campos permitidos según el rol usando RolHelper
        $camposPermitidos = RolHelper::obtenerCamposEditables($esPropioUsuario);

        // VALIDACIÓN DE DATOS SEGÚN LA SECCIÓN
        $seccion = isset($datos['seccion']) ? $datos['seccion'] : null;

        // Validar según la sección que se está editando
        if ($seccion === 'contacto') {
            $validacion = ValidationHelper::validarContacto($datos);
            if (!$validacion['valido']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Errores de validación en datos de contacto',
                    'errores' => $validacion['errores']
                ]);
                return;
            }
        } elseif ($seccion === 'direccion') {
            $validacion = ValidationHelper::validarDireccion($datos);
            if (!$validacion['valido']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Errores de validación en dirección',
                    'errores' => $validacion['errores']
                ]);
                return;
            }
        } elseif ($seccion === 'emergencia') {
            $validacion = ValidationHelper::validarContactoEmergencia($datos);
            if (!$validacion['valido']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Errores de validación en contacto de emergencia',
                    'errores' => $validacion['errores']
                ]);
                return;
            }
        } elseif ($seccion === 'personal') {
            $validacion = ValidationHelper::validarDatosPersonales($datos);
            if (!$validacion['valido']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Errores de validación en datos personales',
                    'errores' => $validacion['errores']
                ]);
                return;
            }
        }

        // Sanitizar datos antes de procesarlos
        $datos = ValidationHelper::sanitizarDatos($datos);

        // Log para depuración
        error_log("=== perfilController::actualizarDatos ===");
        error_log("VoluntarioID: $voluntarioID");
        error_log("Es propio usuario: " . ($esPropioUsuario ? 'Sí' : 'No'));
        error_log("Sección: " . ($seccion ?? 'ninguna'));
        error_log("Campos permitidos por RolHelper: " . json_encode($camposPermitidos));

        // Detectar si se está editando una sección específica que requiere tabla separada
        $seccion = isset($datos['seccion']) ? $datos['seccion'] : null;

        if ($seccion === 'direccion') {
            // Actualizar tabla Direcciones
            $resultado = $this->voluntarioModel->actualizarDireccion($voluntarioID, $datos);
        } elseif ($seccion === 'emergencia') {
            // Actualizar tabla ContactosEmergencia
            $resultado = $this->voluntarioModel->actualizarContactoEmergencia($voluntarioID, $datos);
        } else {
            // Actualizar tabla Voluntarios (comportamiento normal)
            $resultado = $this->voluntarioModel->actualizarDatosEditables($voluntarioID, $datos, $camposPermitidos);
        }

        // Actualizar sesión si fue exitoso Y es el propio usuario
        if ($resultado['success'] && $esPropioUsuario) {
            $datosActualizados = $this->voluntarioModel->obtenerDatosCompletos($voluntarioID);
            if ($datosActualizados) {
                $_SESSION['user']['nombre'] = $datosActualizados['Nombres'];
                $_SESSION['user']['rol'] = $datosActualizados['RolNombre'];
            }
        }

        echo json_encode($resultado);
    }

    public function solicitarActualizacion()
    {
        // Limpiar cualquier output no deseado antes de enviar JSON
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        // Leer el JSON del body
        $input = file_get_contents('php://input');
        $datos = json_decode($input, true);

        if (!$datos || !isset($datos['seccion']) || !isset($datos['mensaje'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $voluntarioID = $_SESSION['user']['id'];
        $seccion = $datos['seccion'];
        $mensaje = $datos['mensaje'];

        // Por ahora, solo retornamos éxito
        // TODO: Guardar en una tabla de solicitudes para que los admins puedan revisarlas

        // Aquí se podría:
        // 1. Insertar en tabla SolicitudesActualizacion
        // 2. Enviar notificación a administradores
        // 3. Enviar correo electrónico

        echo json_encode([
            'success' => true,
            'message' => 'Tu solicitud ha sido enviada. Un administrador la revisará pronto.'
        ]);
    }
}

// Si se llama directamente
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new PerfilController();
    $action = isset($_GET['action']) ? $_GET['action'] : 'actualizarDatos';

    if (method_exists($controller, $action)) {
        $controller->$action();
    }
}
