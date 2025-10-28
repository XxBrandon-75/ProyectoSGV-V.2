<?php
require_once 'config/security.php';
require_once 'controllers/authController.php';

// Verificar si el usuario ya está autenticado
preventAuthAccess();

$authController = new AuthController();

// Procesar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $authController->processLogin();
    } elseif ($action === 'register') {
        $authController->processRegister();
    } elseif (isset($_POST['clear_form_data'])) {
        $authController->clearFormData();
    }
}

$authController->showLoginForm();
