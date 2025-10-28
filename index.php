<?php
// Cargar configuración de seguridad (cabeceras HTTP, sesiones seguras, etc.)
require_once 'config/security.php';

require_once 'controllers/authController.php';

$controller = isset($_GET['controller']) ? $_GET['controller'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

if (!($controller === 'auth' && $action === 'logout')) {
    requireAuth(); // No quitar, sirve para q no se pueda acceder a ningun lado mas sin antes haber iniciado sesion
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
