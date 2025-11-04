<?php

class NotificacionesController
{
    private $user_role;
    private $base_url;

    // Definir los roles y sus niveles de acceso
    private $roles = [
        'Voluntario' => 1,
        'Coordinador de Area' => 2,
        'Administrador' => 3,
        'Superadministrador' => 4
    ];

    public function __construct()
    {
        require_once __DIR__ . '/../config/security.php';

        // Calcular la ruta base automáticamente
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
        return $this->roles[$this->user_role] >= $nivelMinimo;
    }

    public function index()
    {
        // Obtener el rol del usuario desde la sesión
        $rolUsuario = $_SESSION['user']['rol'] ?? 'Voluntario';
        $esCoordinadorOMas = $this->tienePermiso(2);

        // Cargar el modelo de notificaciones
<<<<<<< HEAD
        require_once 'models/Notificacion.php'; 
=======
        require_once 'models/Notificacion.php';
>>>>>>> c233d19cbec062fb8ce596706d82b95497b92612
        $notificacionModel = new Notificacion();

        // Variables para la vista
        $notificacionesPendientes = [];
        $totalPendientes = 0;
        $tramitesSolicitados = [];
        $totalTramites = 0;
<<<<<<< HEAD
=======
        $expedientesPendientes = [];
        $totalExpedientes = 0;
        $especialidadesPendientes = [];
        $totalEspecialidades = 0;
>>>>>>> c233d19cbec062fb8ce596706d82b95497b92612

        // Si es coordinador o superior, obtener voluntarios pendientes y trámites
        if ($esCoordinadorOMas) {
            // VOLUNTARIOS PENDIENTES
            $voluntariosPendientes = $notificacionModel->getVoluntariosSinAprobar();
<<<<<<< HEAD
            
=======

>>>>>>> c233d19cbec062fb8ce596706d82b95497b92612
            foreach ($voluntariosPendientes as $voluntario) {
                $notificacionesPendientes[] = [
                    'id' => $voluntario['VoluntarioID'],
                    'nombre' => trim($voluntario['Nombres'] . ' ' . $voluntario['ApellidoPaterno']),
                    'email' => $voluntario['Email'],
                    'estatus' => $voluntario['EstatusNombre'],
                    'delegacion' => $voluntario['DelegacionNombre'] ?? 'Sin asignar',
                    'area' => $voluntario['AreaNombre'] ?? 'Sin asignar',
                    'fecha_registro' => $voluntario['FechaRegistro'] ?? null,
                ];
            }

            $totalPendientes = count($notificacionesPendientes);

            // TRÁMITES SOLICITADOS
<<<<<<< HEAD
        $tramitesPendientes = $notificacionModel->getTramitesSolicitados();

        foreach ($tramitesPendientes as $tramite) {
            $tramitesSolicitados[] = [
                'SolicitudID' => $tramite['SolicitudID'],
                'VoluntarioID' => $tramite['VoluntarioID'],
                'Nombres' => $tramite['Nombres'],
                'email' => $tramite['email'],
                'curp' => $tramite['curp'],
                'FechaSolicitud' => $tramite['FechaSolicitud'],
                'Estatus' => $tramite['Estatus'],
                'NombreTramite' => $tramite['NombreTramite'],
            ];
        }

        $totalTramites = count($tramitesSolicitados);
=======
            $tramitesPendientes = $notificacionModel->getTramitesSolicitados();

            foreach ($tramitesPendientes as $tramite) {
                $tramitesSolicitados[] = [
                    'SolicitudID' => $tramite['SolicitudID'],
                    'VoluntarioID' => $tramite['VoluntarioID'],
                    'Nombres' => $tramite['Nombres'],
                    'email' => $tramite['email'],
                    'curp' => $tramite['curp'],
                    'FechaSolicitud' => $tramite['FechaSolicitud'],
                    'Estatus' => $tramite['Estatus'],
                    'NombreTramite' => $tramite['NombreTramite'],
                ];
            }

            $totalTramites = count($tramitesSolicitados);

            // EXPEDIENTES/DOCUMENTOS PENDIENTES
            $expedientesPendientesData = $notificacionModel->getExpedientesPendientes();

            foreach ($expedientesPendientesData as $expediente) {
                // Obtener la ruta del documento desde el SP
                $rutaOriginal = $expediente['RutaArchivo'] ?? $expediente['rutaarchivo'] ?? '';

                // Convertir ruta absoluta del servidor a ruta relativa para el navegador
                // De: /public/uploads/documentos/archivo.pdf
                // A: public/uploads/documentos/archivo.pdf (relativa desde index.php)
                if (strpos($rutaOriginal, '/public/') === 0) {
                    $rutaCorregida = substr($rutaOriginal, 1); // Remueve el / inicial
                } elseif (strpos($rutaOriginal, 'public/') === 0) {
                    $rutaCorregida = $rutaOriginal; // Ya está en formato correcto
                } else {
                    $rutaCorregida = $rutaOriginal; // Mantener como está
                }

                $expedientesPendientes[] = [
                    'nombre' => $expediente['NombreVoluntario'] ?? $expediente['nombrevoluntario'] ?? 'Sin nombre',
                    'curp' => $expediente['curp'] ?? $expediente['CURP'] ?? '',
                    'rol' => $expediente['Rol'] ?? $expediente['rol'] ?? '',
                    'documento' => $expediente['NombreSubido'] ?? $expediente['nombresubido'] ?? 'Documento',
                    'fecha' => $expediente['FechaSubida'] ?? $expediente['fechasubida'] ?? null,
                    'ruta' => $rutaCorregida,
                ];
            }
            $totalExpedientes = count($expedientesPendientes);

            // ESPECIALIDADES PENDIENTES
            $especialidadesPendientesData = $notificacionModel->getEspecialidadesPendientes();

            foreach ($especialidadesPendientesData as $especialidad) {
                $especialidadesPendientes[] = [
                    'voluntario_documento_id' => $especialidad['VoluntarioDocumentoID'] ?? null,
                    'nombre' => $especialidad['NombreVoluntario'] ?? 'Sin nombre',
                    'curp' => $especialidad['curp'] ?? '',
                    'area' => $especialidad['Area'] ?? 'Sin área',
                    'documento' => $especialidad['NombreDocumentoEspecialidad'] ?? 'Documento de especialidad',
                    'archivo' => $especialidad['NombreArchivo'] ?? '',
                    'fecha' => $especialidad['FechaSubida'] ?? null,
                ];
            }
            $totalEspecialidades = count($especialidadesPendientes);
>>>>>>> c233d19cbec062fb8ce596706d82b95497b92612
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

        // Total general de notificaciones
<<<<<<< HEAD
        $totalNotificacionesGeneral = $totalPendientes + $totalTramites;
=======
        $totalNotificacionesGeneral = $totalPendientes + $totalTramites + $totalExpedientes + $totalEspecialidades;
>>>>>>> c233d19cbec062fb8ce596706d82b95497b92612

        // Configuración para el layout
        $titulo_pagina = "Notificaciones | Red de Voluntarios";
        $styles = [$this->base_url . 'public/css/n.style.css'];
        $scripts = [$this->base_url . 'public/scripts/n.script.js'];

        // Cargar las vistas
        require_once "views/layout/header.php";
        require_once "views/home/notificaciones.php";
        require_once "views/layout/footer.php";
    }
}
