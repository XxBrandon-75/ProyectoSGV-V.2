<?php
class SecurityHelper
{
    /**
     * Genera un token CSRF y lo almacena en la sesión
     * @return string Token generado
     */
    public static function generarTokenCSRF()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        } else {
            // Regenerar token cada 30 minutos
            if (time() - $_SESSION['csrf_token_time'] > 1800) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['csrf_token_time'] = time();
            }
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Valida el token CSRF recibido
     * @param string $token Token a validar
     * @return bool True si es válido, false si no
     */
    public static function validarTokenCSRF($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }

        // Verificar que el token no haya expirado (1 hora)
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            return false;
        }

        // Comparación segura contra timing attacks
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Sanitiza entrada de usuario
     * @param mixed $data Datos a sanitizar
     * @return mixed Datos sanitizados
     */
    public static function sanitizarEntrada($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizarEntrada'], $data);
        }

        // Eliminar espacios al inicio y final
        $data = trim($data);
        // Eliminar barras invertidas
        $data = stripslashes($data);
        // Convertir caracteres especiales a entidades HTML
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        return $data;
    }

    /**
     * Valida que un email sea válido
     * @param string $email Email a validar
     * @return bool
     */
    public static function validarEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida que un teléfono tenga formato correcto (10 dígitos)
     * @param string $telefono Teléfono a validar
     * @return bool
     */
    public static function validarTelefono($telefono)
    {
        return preg_match('/^[0-9]{10}$/', $telefono) === 1;
    }

    /**
     * Valida que un CURP tenga formato correcto
     * @param string $curp CURP a validar
     * @return bool
     */
    public static function validarCURP($curp)
    {
        return preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9]{2}$/', strtoupper($curp)) === 1;
    }

    /**
     * Previene inyección de HTML/JavaScript
     * @param string $string Cadena a limpiar
     * @return string Cadena limpia
     */
    public static function prevenirXSS($string)
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Registra un intento de acceso no autorizado
     * @param string $accion Descripción de la acción
     * @param array $datos Datos adicionales del intento
     */
    public static function registrarIntentoSospechoso($accion, $datos = [])
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'DESCONOCIDA';
        $usuario = $_SESSION['user']['id'] ?? 'NO_AUTENTICADO';
        $timestamp = date('Y-m-d H:i:s');

        $mensaje = sprintf(
            "[SEGURIDAD] %s | Usuario: %s | IP: %s | Datos: %s",
            $accion,
            $usuario,
            $ip,
            json_encode($datos)
        );

        error_log($mensaje);

        // Opcional: Guardar en base de datos para análisis posterior
        // TODO: Implementar tabla de auditoría de seguridad
    }

    /**
     * Limita la tasa de peticiones (Rate Limiting básico)
     * @param string $clave Identificador único (ej: 'login_' . $ip)
     * @param int $limite Número máximo de intentos
     * @param int $ventana Ventana de tiempo en segundos
     * @return bool True si está dentro del límite, false si excede
     */
    public static function verificarRateLimiting($clave, $limite = 5, $ventana = 300)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $ahora = time();
        $claveSession = 'rate_limit_' . $clave;

        if (!isset($_SESSION[$claveSession])) {
            $_SESSION[$claveSession] = [
                'intentos' => 1,
                'primer_intento' => $ahora
            ];
            return true;
        }

        $datos = $_SESSION[$claveSession];

        // Si la ventana de tiempo ha pasado, resetear
        if ($ahora - $datos['primer_intento'] > $ventana) {
            $_SESSION[$claveSession] = [
                'intentos' => 1,
                'primer_intento' => $ahora
            ];
            return true;
        }

        // Incrementar contador
        $_SESSION[$claveSession]['intentos']++;

        // Verificar si excede el límite
        if ($_SESSION[$claveSession]['intentos'] > $limite) {
            self::registrarIntentoSospechoso(
                'RATE_LIMIT_EXCEDIDO',
                ['clave' => $clave, 'intentos' => $_SESSION[$claveSession]['intentos']]
            );
            return false;
        }

        return true;
    }

    /**
     * Hashea una contraseña de forma segura
     * @param string $password Contraseña en texto plano
     * @return string Hash de la contraseña
     */
    public static function hashearPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verifica una contraseña contra su hash
     * @param string $password Contraseña en texto plano
     * @param string $hash Hash almacenado
     * @return bool
     */
    public static function verificarPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
