<?php
ob_start();

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/VoluntarioModel.php';

ob_end_clean();

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$action = $_GET['action'] ?? '';
$voluntarioModel = new VoluntarioModel();

switch ($action) {
    case 'aprobar':
        aprobarVoluntario($voluntarioModel);
        break;

    case 'rechazar':
        rechazarVoluntario($voluntarioModel);
        break;

    case 'detalles':
        obtenerDetallesVoluntario($voluntarioModel);
        break;

    case 'contador':
        obtenerContadorNotificaciones($voluntarioModel);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

function aprobarVoluntario($voluntarioModel)
{
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit();
    }

    // Verificar permisos
    $rolUsuario = $_SESSION['user']['rol'] ?? 'Voluntario';
    $esCoordinadorOMas = in_array($rolUsuario, ['Coordinador de Area', 'Administrador', 'Superadministrador']);

    if (!$esCoordinadorOMas) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción']);
        exit();
    }

    // Obtener datos del POST
    $data = json_decode(file_get_contents('php://input'), true);
    $voluntarioId = $data['voluntarioId'] ?? null;
    
    // Obtener el ID del usuario logueado (quien aprueba)
    $adminId = $_SESSION['user']['id'] ?? null;

    // Validaciones
    if (!$voluntarioId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de voluntario no proporcionado']);
        exit();
    }

    if (!$adminId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No se pudo identificar al usuario que aprueba']);
        exit();
    }

    // Motivo opcional
    $motivo = $data['motivo'] ?? 'Registro validado y aprobado.';

    // Log para debugging
    error_log("NotificacionesAjax - Aprobando voluntario ID: $voluntarioId por admin ID: $adminId");

    // Aprobar usando el procedimiento almacenado
    $resultado = $voluntarioModel->aprobarVoluntario($voluntarioId, $adminId, $motivo);

    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'message' => $resultado['message'],
            'totalPendientes' => $voluntarioModel->contarVoluntariosPendientes()
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => $resultado['message']
        ]);
    }
}

function rechazarVoluntario($voluntarioModel)
{
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit();
    }

    // Verificar permisos
    $rolUsuario = $_SESSION['user']['rol'] ?? 'Voluntario';
    $esCoordinadorOMas = in_array($rolUsuario, ['Coordinador de Area', 'Administrador', 'Superadministrador']);

    if (!$esCoordinadorOMas) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción']);
        exit();
    }

    // Obtener datos del POST
    $data = json_decode(file_get_contents('php://input'), true);
    $voluntarioId = $data['voluntarioId'] ?? null;
    $motivo = $data['motivo'] ?? '';
    
    // Obtener el ID del usuario logueado
    $adminId = $_SESSION['user']['id'] ?? null;

    // Validaciones
    if (!$voluntarioId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de voluntario no proporcionado']);
        exit();
    }

    if (!$adminId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No se pudo identificar al usuario que rechaza']);
        exit();
    }

    // El motivo es OBLIGATORIO
    if (empty(trim($motivo))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El motivo del rechazo es obligatorio']);
        exit();
    }

    // Log para debugging
    error_log("NotificacionesAjax - Rechazando voluntario ID: $voluntarioId por admin ID: $adminId. Motivo: $motivo");

    // Rechazar usando el procedimiento almacenado
    $resultado = $voluntarioModel->rechazarVoluntario($voluntarioId, $adminId, $motivo);

    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'message' => $resultado['message'],
            'totalPendientes' => $voluntarioModel->contarVoluntariosPendientes()
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => $resultado['message']
        ]);
    }
}

function obtenerDetallesVoluntario($voluntarioModel)
{
    $voluntarioID = $_GET['id'] ?? 0;

    if (!$voluntarioID) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
        return;
    }

    // Log para debugging
    error_log("NotificacionesAjax - Obteniendo detalles del voluntario ID: $voluntarioID");

    $voluntario = $voluntarioModel->getVoluntarioById($voluntarioID);

    if ($voluntario) {
        echo json_encode(['success' => true, 'data' => $voluntario]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Voluntario no encontrado']);
    }
}

/**
 * Obtiene el contador de notificaciones pendientes
 * NUEVO ENDPOINT para el badge del header
 */
function obtenerContadorNotificaciones($voluntarioModel)
{
    $rolUsuario = $_SESSION['user']['rol'] ?? 'Voluntario';
    $esCoordinadorOMas = in_array($rolUsuario, ['Coordinador de Area', 'Administrador', 'Superadministrador']);

    // Log para debugging
    error_log("NotificacionesAjax - Obteniendo contador para rol: $rolUsuario");
    error_log("NotificacionesAjax - Es coordinador o más: " . ($esCoordinadorOMas ? 'Sí' : 'No'));

    if (!$esCoordinadorOMas) {
        error_log("NotificacionesAjax - Usuario no tiene permisos suficientes");
        echo json_encode([
            'success' => true,
            'totalPendientes' => 0,
            'rolUsuario' => $rolUsuario
        ]);
        return;
    }

    // Obtener el contador de voluntarios pendientes
    $totalPendientes = $voluntarioModel->contarVoluntariosPendientes();

    // Log para debugging
    error_log("NotificacionesAjax - Total pendientes: $totalPendientes");

    echo json_encode([
        'success' => true,
        'totalPendientes' => $totalPendientes,
        'rolUsuario' => $rolUsuario
    ]);
}