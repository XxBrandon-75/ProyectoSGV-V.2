<?php
ob_start();

require_once __DIR__ . '/../helpers/CatalogoHelper.php';

class CatalogoController
{
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
    }

    public function obtenerTodos()
    {
        // Limpiar cualquier output no deseado antes de enviar JSON
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        try {
            $catalogos = [
                'estados' => CatalogoHelper::obtenerEstados(),
                'ciudades' => CatalogoHelper::obtenerCiudades(),
                'estadosCiviles' => CatalogoHelper::obtenerEstadosCiviles(),
                'gruposSanguineos' => CatalogoHelper::obtenerGruposSanguineos(),
                'areas' => CatalogoHelper::obtenerAreas(),
                'delegaciones' => CatalogoHelper::obtenerDelegaciones(),
                'roles' => CatalogoHelper::obtenerRoles(),
            ];

            echo json_encode([
                'success' => true,
                'catalogos' => $catalogos
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener catálogos: ' . $e->getMessage()
            ]);
        }
    }
}

// Si se llama directamente
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new CatalogoController();
    $action = isset($_GET['action']) ? $_GET['action'] : 'obtenerTodos';

    if (method_exists($controller, $action)) {
        $controller->$action();
    }
}
