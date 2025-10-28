<?php
ob_start();

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/voluntario.php';

ob_end_clean();

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$action = $_GET['action'] ?? '';
$voluntarioModel = new Voluntario();

switch ($action) {
    case 'obtenerDetalles':
        obtenerDetallesVoluntario($voluntarioModel);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

function obtenerDetallesVoluntario($voluntarioModel)
{
    $voluntarioID = $_GET['id'] ?? 0;

    if (!$voluntarioID) {
        http_response_code(400);
        echo json_encode(['error' => 'ID no proporcionado']);
        return;
    }

    // Verificar permisos según el rol del usuario
    $rolUsuario = $_SESSION['user']['rol'] ?? 'Voluntario';
    $idUsuario = $_SESSION['user']['id'];

    // Log para depuración
    error_log("voluntarioAjaxController - Buscando datos para ID: " . $voluntarioID);
    error_log("voluntarioAjaxController - Usuario solicitante: ID=" . $idUsuario . ", Rol=" . $rolUsuario);

    // Obtener datos del voluntario solicitado
    $datosVoluntario = $voluntarioModel->obtenerDatosCompletos($voluntarioID);

    if (!$datosVoluntario) {
        error_log("voluntarioAjaxController - ERROR: No se encontraron datos para ID: " . $voluntarioID);
        http_response_code(404);
        echo json_encode(['error' => 'Voluntario no encontrado']);
        return;
    }

    // Log de datos obtenidos
    error_log("voluntarioAjaxController - Datos obtenidos. Rol del voluntario: " . ($datosVoluntario['RolNombre'] ?? 'N/A'));

    // Control de visibilidad según roles
    $rolVoluntario = $datosVoluntario['RolNombre'] ?? 'Voluntario';

    // Superadministrador es invisible para todos excepto él mismo
    if ($rolVoluntario === 'Superadministrador' && $idUsuario != $voluntarioID) {
        error_log("voluntarioAjaxController - Acceso denegado: Intentando ver Superadministrador");
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permisos para ver este perfil']);
        return;
    }

    // Administrador es invisible para todos excepto Superadministrador
    if ($rolVoluntario === 'Administrador' && $rolUsuario !== 'Superadministrador' && $idUsuario != $voluntarioID) {
        error_log("voluntarioAjaxController - Acceso denegado: Intentando ver Administrador sin ser Superadmin");
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permisos para ver este perfil']);
        return;
    }

    // Coordinadores solo pueden ver voluntarios de su delegación
    if ($rolUsuario === 'Coordinador de Area') {
        $datosUsuario = $voluntarioModel->obtenerDatosCompletos($idUsuario);
        if ($datosUsuario['DelegacionID'] !== $datosVoluntario['DelegacionID'] && $idUsuario != $voluntarioID) {
            error_log("voluntarioAjaxController - Acceso denegado: Coordinador intentando ver voluntario de otra delegación");
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permisos para ver este perfil']);
            return;
        }
    }

    // Voluntarios solo pueden ver su propio perfil
    if ($rolUsuario === 'Voluntario' && $idUsuario != $voluntarioID) {
        error_log("voluntarioAjaxController - Acceso denegado: Voluntario intentando ver otro perfil");
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permisos para ver este perfil']);
        return;
    }

    // Retornar los datos
    error_log("voluntarioAjaxController - Acceso permitido. Devolviendo datos.");
    echo json_encode([
        'success' => true,
        'datos' => $datosVoluntario
    ]);
}
