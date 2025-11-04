<?php
ob_start();

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Notificacion.php';

ob_end_clean();

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$action = $_GET['action'] ?? '';
$notificacionModel = new Notificacion();

switch ($action) {
    case 'aprobar':
        aprobarVoluntario($notificacionModel);
        break;

    case 'rechazar':
        rechazarVoluntario($notificacionModel);
        break;

    case 'detalles':
        obtenerDetallesVoluntario($notificacionModel);
        break;

    case 'contador':
        obtenerContadorNotificaciones($notificacionModel);
        break;

    // NUEVOS CASOS PARA TRÁMITES
    case 'detalles-tramite':
        obtenerDetallesTramite($notificacionModel);
        break;

    case 'aprobar-tramite':
        aprobarTramite($notificacionModel);
        break;

    case 'rechazar-tramite':
        rechazarTramite($notificacionModel);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

// ====================================================================
// FUNCIONES EXISTENTES PARA VOLUNTARIOS
// ====================================================================

function aprobarVoluntario($voluntarioModel)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit();
    }

    $rolUsuario = $_SESSION['user']['rol'] ?? 'Voluntario';
    $esCoordinadorOMas = in_array($rolUsuario, ['Coordinador de Area', 'Administrador', 'Superadministrador']);

    if (!$esCoordinadorOMas) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción']);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $voluntarioId = $data['voluntarioId'] ?? null;

    // Obtener el ID del usuario logueado (quien aprueba)
    $adminId = $_SESSION['user']['id'] ?? null;

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

    $motivo = $data['motivo'] ?? 'Registro validado y aprobado.';
    error_log("NotificacionesAjax - Aprobando voluntario ID: $voluntarioId por admin ID: $adminId");

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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit();
    }

    $rolUsuario = $_SESSION['user']['rol'] ?? 'Voluntario';
    $esCoordinadorOMas = in_array($rolUsuario, ['Coordinador de Area', 'Administrador', 'Superadministrador']);

    if (!$esCoordinadorOMas) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción']);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $voluntarioId = $data['voluntarioId'] ?? null;
    $motivo = $data['motivo'] ?? '';

    // Obtener el ID del usuario logueado
    $adminId = $_SESSION['user']['id'] ?? null;

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

    if (empty(trim($motivo))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El motivo del rechazo es obligatorio']);
        exit();
    }

    error_log("NotificacionesAjax - Rechazando voluntario ID: $voluntarioId por admin ID: $adminId. Motivo: $motivo");

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

    error_log("NotificacionesAjax - Obteniendo detalles del voluntario ID: $voluntarioID");

    $voluntario = $voluntarioModel->getVoluntarioById($voluntarioID);

    if ($voluntario) {
        echo json_encode(['success' => true, 'data' => $voluntario]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Voluntario no encontrado']);
    }
}

function obtenerContadorNotificaciones($voluntarioModel)
{
    $rolUsuario = $_SESSION['user']['rol'] ?? 'Voluntario';
    $esCoordinadorOMas = in_array($rolUsuario, ['Coordinador de Area', 'Administrador', 'Superadministrador']);

    error_log("NotificacionesAjax - Obteniendo contador para rol: $rolUsuario");
    error_log("NotificacionesAjax - Es coordinador o más: " . ($esCoordinadorOMas ? 'Sí' : 'No'));

    if (!$esCoordinadorOMas) {
        error_log("NotificacionesAjax - Usuario no tiene permisos suficientes");
        echo json_encode([
            'success' => true,
            'totalPendientes' => 0,
            'expedientesPendientes' => 0,
            'rolUsuario' => $rolUsuario
        ]);
        return;
    }

    $totalPendientes = $voluntarioModel->contarVoluntariosPendientes();
    $totalTramites = $voluntarioModel->contarTramitesSolicitados();
    $totalGeneral = $totalPendientes + $totalTramites;

    // Obtener el contador de expedientes pendientes
    $expedientesPendientes = $voluntarioModel->contarExpedientesPendientes();

    // Log para debugging
    error_log("NotificacionesAjax - Total pendientes: $totalPendientes");
    error_log("NotificacionesAjax - Expedientes pendientes: $expedientesPendientes");

    echo json_encode([
        'success' => true,
        'totalPendientes' => $totalPendientes,
        'expedientesPendientes' => $expedientesPendientes,
        'rolUsuario' => $rolUsuario
    ]);
}
