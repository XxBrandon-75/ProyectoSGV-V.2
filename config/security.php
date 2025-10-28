<?php
// Prevenir que el navegador interprete archivos como un tipo MIME diferente
header("X-Content-Type-Options: nosniff");

// Prevenir clickjacking - no permitir que el sitio se muestre en un iframe
header("X-Frame-Options: DENY");

// Activar protección XSS del navegador
header("X-XSS-Protection: 1; mode=block");

// Política de referrer - solo enviar el origen en peticiones cross-origin
header("Referrer-Policy: strict-origin-when-cross-origin");

// Forzar HTTPS (solo si el sitio está en HTTPS)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

// Content Security Policy (CSP) - Política de seguridad de contenido
$csp = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://kit.fontawesome.com https://cdn.jsdelivr.net",
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://ka-f.fontawesome.com",
    "font-src 'self' https://fonts.gstatic.com https://ka-f.fontawesome.com",
    "img-src 'self' data: https:",
    "connect-src 'self' https://ka-f.fontawesome.com",
    "frame-ancestors 'none'",
    "base-uri 'self'",
    "form-action 'self'"
];

// Comentar la siguiente línea si causa problemas con scripts inline
// header("Content-Security-Policy: " . implode('; ', $csp));

// Configuración de cookies de sesión seguras
if (session_status() === PHP_SESSION_NONE) {
    // Solo iniciar sesión si no está activa
    ini_set('session.cookie_httponly', 1);  // Las cookies no son accesibles vía JavaScript
    ini_set('session.cookie_samesite', 'Strict');  // Prevenir CSRF

    // Solo establecer secure si estamos en HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }

    // Nombre de sesión personalizado (más difícil de identificar)
    session_name('SGV_SESSION');

    // Tiempo de expiración de sesión (30 minutos de inactividad)
    ini_set('session.gc_maxlifetime', 1800);

    session_start();

    // Regenerar ID de sesión periódicamente para prevenir session fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerar cada 30 minutos
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Deshabilitar la exposición de la versión de PHP
header_remove("X-Powered-By");

// Prevenir que el navegador cachee páginas con información sensible
// Descomentar si es necesario para páginas con datos sensibles
// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Pragma: no-cache");
// header("Expires: 0");
