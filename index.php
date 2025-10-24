<?php

$controller = isset($_GET['controller']) ? $_GET['controller'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';


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