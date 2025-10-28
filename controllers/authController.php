<?php
function requireAuth()
{
    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['user'])) {
        // Calcular base_url dinámicamente
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
        $protocol = $isHttps ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $script = dirname($_SERVER['SCRIPT_NAME']);
        $base_url = $protocol . $host . ($script != '/' ? $script : '') . '/';

        // Redirigir al login si no está autenticado
        header('Location: ' . $base_url . 'login.php');
        exit();
    }
}

function preventAuthAccess()
{
    // Si ya está autenticado, redirigir al dashboard
    if (isset($_SESSION['user'])) {
        // Calcular base_url dinámicamente
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
        $protocol = $isHttps ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $script = dirname($_SERVER['SCRIPT_NAME']);
        $base_url = $protocol . $host . ($script != '/' ? $script : '') . '/';

        header('Location: ' . $base_url . 'index.php');
        exit();
    }
}

// Controlador de autenticación
class AuthController
{
    private function getBaseUrl()
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
        $protocol = $isHttps ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $script = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . $host . ($script != '/' ? $script : '') . '/';
    }

    /**
     * Procesa el login del usuario
     */
    public function processLogin()
    {
        require_once __DIR__ . '/../models/voluntario.php';
        require_once __DIR__ . '/../config/database.php';
        require_once __DIR__ . '/voluntariocontroller.php';

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $voluntario = new VoluntarioController();
        $resultado = $voluntario->login($email, $password);

        if ($resultado['success']) {
            $_SESSION['user'] = $resultado['user'];
            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);
            header('Location: ' . $this->getBaseUrl() . 'index.php');
            exit;
        } else {
            $_SESSION['loginError'] = $resultado['message'];
            $_SESSION['showRegisterForm'] = false;
            header('Location: ' . $this->getBaseUrl() . 'login.php');
            exit;
        }
    }

    /**
     * Procesa el registro de un nuevo usuario
     */
    public function processRegister()
    {
        require_once __DIR__ . '/../models/voluntario.php';
        require_once __DIR__ . '/../config/database.php';
        require_once __DIR__ . '/voluntariocontroller.php';

        // Guardar datos del formulario en sesión
        $_SESSION['form_data'] = $_POST;

        $voluntario = new VoluntarioController();
        $resultado = $voluntario->register($_POST);

        if ($resultado['success']) {
            $_SESSION['registerSuccess'] = $resultado['message'];
            $_SESSION['showRegisterForm'] = true;
            // Limpiar datos del formulario si fue exitoso
            unset($_SESSION['form_data']);
            header('Location: ' . $this->getBaseUrl() . 'login.php');
            exit;
        } else {
            $_SESSION['registerError'] = $resultado['message'];
            $_SESSION['showRegisterForm'] = true;
            header('Location: ' . $this->getBaseUrl() . 'login.php');
            exit;
        }
    }

    /**
     * Muestra el formulario de login/registro
     */
    public function showLoginForm()
    {
        require_once __DIR__ . '/../helpers/CatalogoHelper.php';

        // Recuperar mensajes de la sesión
        $loginError = $_SESSION['loginError'] ?? '';
        $registerError = $_SESSION['registerError'] ?? '';
        $registerSuccess = $_SESSION['registerSuccess'] ?? '';
        $showRegisterForm = $_SESSION['showRegisterForm'] ?? false;
        $formData = $_SESSION['form_data'] ?? [];

        // Limpiar mensajes de la sesión después de leerlos
        unset($_SESSION['loginError']);
        unset($_SESSION['registerError']);
        unset($_SESSION['registerSuccess']);
        unset($_SESSION['showRegisterForm']);

        // Obtener catálogos
        $gruposSanguineos = CatalogoHelper::obtenerGruposSanguineos();
        $catCiudades = CatalogoHelper::obtenerCiudades();
        $catEstadoCivil = CatalogoHelper::obtenerEstadosCiviles();

        // Incluir la vista
        require_once __DIR__ . '/../views/auth/login.view.php';
    }

    /**
     * Limpia los datos del formulario
     */
    public function clearFormData()
    {
        unset($_SESSION['form_data']);
        exit;
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout()
    {
        // Limpiar todas las variables de sesión
        $_SESSION = array();

        // Borrar la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header('Location: ' . $this->getBaseUrl() . 'login.php');
        exit();
    }
}
