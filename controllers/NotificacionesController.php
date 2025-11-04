<?php

class NotificacionesController
{
    private $user_role;
    private $base_url;

    // Definir los roles y sus niveles de acceso (Copia de HomeController)
    private $roles = [
        'Voluntario' => 1,
        'Coordinador de Area' => 2,
        'Administrador' => 3,
        'Superadministrador' => 4
    ];

    public function __construct()
    {
        // La inclusión de security.php debe ser la primera
        require_once __DIR__ . '/../config/security.php';

        // Calcular la ruta base automáticamente (Copia de HomeController)
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');

        $protocol = $isHttps ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $script = dirname($_SERVER['SCRIPT_NAME']);
        $this->base_url = $protocol . $host . ($script != '/' ? $script : '') . '/';

        // Verificar si el usuario está logueado
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $this->base_url . 'login.php');
            exit();
        }

        // Obtener el rol del usuario de la sesión
        $this->user_role = isset($_SESSION['user']['rol']) ? $_SESSION['user']['rol'] : 'Voluntario';
    }

    private function tienePermiso($nivelMinimo)
    {
        // Se puede mantener aquí o mover a un Helper si se usa mucho
        return $this->roles[$this->user_role] >= $nivelMinimo;
    }

    public function index()
    {
        // Implementación de la lógica de notificaciones

        // Obtener el rol del usuario desde la sesión
        $rolUsuario = $_SESSION['user']['rol'] ?? 'Voluntario';
        $esCoordinadorOMas = $this->tienePermiso(2); // Usamos el método de la clase

        // Cargar el modelo de voluntarios
        // Asumiendo que VoluntarioModel.php está en models/
        require_once 'models/Notificacion.php';
        $notificacionModel = new Notificacion();

        // Variables para la vista - Separadas por tipo
        $voluntariosPendientesArray = [];
        $expedientesPendientesArray = [];
        $totalVoluntariosPendientes = 0;
        $totalExpedientesPendientes = 0;

        // Si es coordinador o superior, obtener voluntarios pendientes
        if ($esCoordinadorOMas) {
            $voluntariosPendientes = $notificacionModel->getVoluntariosSinAprobar();

            // Formatear los datos para la vista
            foreach ($voluntariosPendientes as $voluntario) {
                $voluntariosPendientesArray[] = [
                    'id' => $voluntario['VoluntarioID'],
                    'nombre' => trim($voluntario['Nombres'] . ' ' . $voluntario['ApellidoPaterno']),
                    'email' => $voluntario['Email'],
                    'estatus' => $voluntario['EstatusNombre'],
                    'delegacion' => $voluntario['DelegacionNombre'] ?? 'Sin asignar',
                    'area' => $voluntario['AreaNombre'] ?? 'Sin asignar',
                    'fecha_registro' => $voluntario['FechaRegistro'] ?? null,
                ];
            }

            // Obtener expedientes pendientes
            $expedientesPendientes = $notificacionModel->getExpedientesPendientes();

            // Formatear los expedientes para la vista
            foreach ($expedientesPendientes as $expediente) {
                $expedientesPendientesArray[] = [
                    'voluntario_id' => $expediente['VoluntarioID'] ?? null,
                    'nombre' => $expediente['NombreVoluntario'],
                    'curp' => $expediente['curp'] ?? '',
                    'rol' => $expediente['Rol'] ?? '',
                    'documento' => $expediente['NombreSubido'] ?? 'Documento',
                    'fecha' => $expediente['FechaSubida'] ?? null,
                    'ruta' => $expediente['RutaArchivo'] ?? '',
                ];
            }

            $totalVoluntariosPendientes = count($voluntariosPendientesArray);
            $totalExpedientesPendientes = count($expedientesPendientesArray);
        }

        // Notificaciones generales del sistema
        $notificacionesGenerales = [
            [
                'tipo' => 'info',
                'titulo' => 'Actualización del sistema',
                'mensaje' => 'Se han implementado mejoras en el sistema de gestión de voluntarios.',
                'tiempo' => 'Hace 2 horas',
                'icono' => 'fa-circle-info'
            ],
            [
                'tipo' => 'evento',
                'titulo' => 'Próxima reunión de coordinadores',
                'mensaje' => 'La reunión mensual de coordinadores será el próximo viernes a las 10:00 AM.',
                'tiempo' => 'Hace 5 horas',
                'icono' => 'fa-calendar'
            ]
        ];

        // Configuración para el layout
        $titulo_pagina = "Notificaciones | Red de Voluntarios";
        $styles = [$this->base_url . 'public/css/n.style.css'];
        $scripts = [$this->base_url . 'public/scripts/n.script.js'];

        // Cargar las vistas
        // NOTA: Asegúrate de que 'views/home/notificaciones.php' sea movida 
        // o copiada a 'views/notificaciones/index.php' si sigues la convención. 
        // Por ahora, mantendremos la ruta original para mínima modificación.
        require_once "views/layout/header.php";
        require_once "views/home/notificaciones.php";
        require_once "views/layout/footer.php";
    }
}
