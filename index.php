<?php
// Cargar configuración de seguridad (cabeceras HTTP, sesiones seguras, etc.)
require_once 'config/security.php';
require_once 'controllers/authController.php';

$controller = isset($_GET['controller']) ? $_GET['controller'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// ✅ NUEVO: Mapeo de acciones especiales para trámites
$actionMap = [
    'guardar_tramite' => ['controller' => 'tramite', 'action' => 'guardarTramite'],
    'modificar_tramite' => ['controller' => 'tramite', 'action' => 'modificarTramite'],
    'eliminar_tramite' => ['controller' => 'tramite', 'action' => 'eliminarTramite'],
    'ver_tramites' => ['controller' => 'tramite', 'action' => 'obtenerTramitesJSON'],
    'ver_requerimientos' => ['controller' => 'tramite', 'action' => 'obtenerRequerimientos'],
    'iniciar_solicitud' => ['controller' => 'tramite', 'action' => 'iniciar'],
    'guardar_solicitud' => ['controller' => 'tramite', 'action' => 'guardar'],
    'obtener_datos_solicitud' => ['controller' => 'tramite', 'action' => 'obtenerDatosSolicitud']
];

// ✅ Si la acción está en el mapeo, cambiar controller y action
if (isset($actionMap[$action])) {
    $controller = $actionMap[$action]['controller'];
    $action = $actionMap[$action]['action'];
}

$controllerName = ucfirst($controller) . 'Controller';
$controllerFile = "controllers/" . $controllerName . ".php";

if (!file_exists($controllerFile)) {
    echo "Error: El controlador <b>" . $controllerName . "</b> no existe.";
    exit();
}

require_once $controllerFile;

if (!class_exists($controllerName)) {
    echo "Error: La clase <b>" . $controllerName . "</b> no está definida.";
    exit();
}

$controllerObj = new $controllerName();

if (!method_exists($controllerObj, $action)) {
    echo "Error: La acción <b>" . $action . "</b> no existe en el controlador <b>" . $controllerName . "</b>.";
    exit();
}

$controllerObj->$action();
?>