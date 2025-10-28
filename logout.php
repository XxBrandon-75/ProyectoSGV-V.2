<?php
require_once 'config/security.php';
require_once 'controllers/authController.php';

// Instanciar el controlador y ejecutar logout
$authController = new AuthController();
$authController->logout();
