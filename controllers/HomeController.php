<?php
class HomeController
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

        // Calcular la ruta base automáticamente (compatible con Azure y proxies)
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
        $titulo_pagina = "Inicio RVS | Red de Voluntarios";

        $styles = [$this->base_url . 'public/css/i.style.css'];

        $scripts = [$this->base_url . 'public/scripts/i.script.js'];

        require_once "views/layout/header.php";

        require_once "views/home/index.php";

        require_once "views/layout/footer.php";
    }

    public function especialidades()
    {
        // Variables de permisos
        $ver_cont_gest = $this->tienePermiso(2); // Coordinador o superior puede agregar especialidades
        $ver_card_edit = $this->tienePermiso(2); // Coordinador o superior puede editar/eliminar

        $titulo_pagina = "Especialidades | Red de Voluntarios";

        $styles = [$this->base_url . 'public/css/e.style.css'];

        $scripts = [$this->base_url . 'public/scripts/e.script.js'];

        require_once "views/layout/header.php";

        require_once "views/home/especialidades.php";

        require_once "views/layout/footer.php";
    }

    public function tramites()
    {
        // Variables de permisos
        $ver_cont_gest = $this->tienePermiso(2); // Coordinador o superior puede agregar trámites
        $ver_card_edit = $this->tienePermiso(2); // Coordinador o superior puede editar/eliminar

        $titulo_pagina = "Trámites | Red de Voluntarios";

        $styles = [$this->base_url . 'public/css/t.style.css'];

        $scripts = [$this->base_url . 'public/scripts/t.script.js'];

        require_once "views/layout/header.php";

        require_once "views/home/tramites.php";

        require_once "views/layout/footer.php";
    }

    public function documentacion()
    {
        // Variables de permisos
        $ver_cont_gest = $this->tienePermiso(2); // Coordinador o superior puede agregar documentos
        $ver_card_edit = $this->tienePermiso(2); // Coordinador o superior puede editar/eliminar

        $titulo_pagina = "Documentación | Red de Voluntarios";

        $styles = [$this->base_url . 'public/css/d.style.css'];

        $scripts = [$this->base_url . 'public/scripts/d.script.js'];

        require_once "views/layout/header.php";

        require_once "views/home/documentacion.php";

        require_once "views/layout/footer.php";
    }

    public function perfil()
    {
        require_once 'models/voluntario.php';
        require_once 'config/database.php';
        require_once 'helpers/RolHelper.php';
        require_once 'helpers/CatalogoHelper.php';

        $voluntarioModel = new Voluntario();

        // Verificar si se está viendo el perfil de otro usuario
        $voluntarioID = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user']['id'];
        $esPropioUsuario = ($voluntarioID === $_SESSION['user']['id']);

        // Obtener datos completos del voluntario
        $datosUsuario = $voluntarioModel->obtenerDatosCompletos($voluntarioID);

        if (!$datosUsuario) {
            // Si no se encuentran datos, redirigir al propio perfil
            header('Location: ' . $this->base_url . 'index.php?controller=home&action=perfil');
            exit();
        }

        // Verificar permisos para ver este perfil
        if (!$esPropioUsuario && !RolHelper::puedeVerPerfil($datosUsuario)) {
            // No tiene permisos, redirigir al propio perfil
            $_SESSION['error'] = 'No tienes permisos para ver este perfil';
            header('Location: ' . $this->base_url . 'index.php?controller=home&action=perfil');
            exit();
        }

        // Variables de permisos
        $puedeModificar = RolHelper::puedeModificarPerfil($datosUsuario);
        $puedeEditarRol = RolHelper::puedeEditarRoles();
        $puedeVerVoluntarios = RolHelper::puedeVerCoordinadores();

        // Obtener catálogos para direcciones
        $catCiudades = CatalogoHelper::obtenerCiudades();
        $catEstados = CatalogoHelper::obtenerEstados();

        $titulo_pagina = $esPropioUsuario ? "Mi Perfil | Red de Voluntarios" : "Perfil de " . $datosUsuario['Nombres'] . " | Red de Voluntarios";

        $styles = [$this->base_url . 'public/css/p.style.css'];

        $scripts = [
            $this->base_url . 'public/scripts/p.config.js',
            $this->base_url . 'public/scripts/p.script.js'
        ];

        require_once "views/layout/header.php";

        require_once "views/home/perfil.php";

        require_once "views/layout/footer.php";
    }

    public function miCargo()
    {
        // Solo coordinadores o superiores
        if (!$this->tienePermiso(2)) {
            header('Location: ' . $this->base_url . 'index.php?controller=home&action=perfil');
            exit();
        }

        require_once 'models/voluntario.php';
        require_once 'config/database.php';

        $voluntarioModel = new Voluntario();
        $usuario_id = $_SESSION['user']['id'];

        // Obtener datos del coordinador para saber su delegación
        $datosCoordinador = $voluntarioModel->obtenerDatosCompletos($usuario_id);

        // Obtener voluntarios de su delegación
        $voluntariosCargo = [];
        if ($datosCoordinador && isset($datosCoordinador['DelegacionID'])) {
            $voluntariosCargo = $voluntarioModel->obtenerVoluntariosPorDelegacion($datosCoordinador['DelegacionID']);
        }

        // Variables de permisos
        $puedeEditarDatosBasicos = true;
        $puedeEditarRol = $this->tienePermiso(3);
        $puedeVerVoluntarios = $this->tienePermiso(2);
        $puedeEditarOtros = $this->tienePermiso(3);

        $titulo_pagina = "Voluntarios a mi Cargo | Red de Voluntarios";

        $styles = [$this->base_url . 'public/css/p.style.css'];

        $scripts = [
            $this->base_url . 'public/scripts/p.config.js',
            $this->base_url . 'public/scripts/p.script.js'
        ];

        require_once "views/layout/header.php";

        require_once "views/home/miCargo.php";

        require_once "views/layout/footer.php";
    }

    public function coordinadores()
    {
        // Solo administradores o superiores
        if (!$this->tienePermiso(3)) {
            header('Location: ' . $this->base_url . 'index.php?controller=home&action=perfil');
            exit();
        }

        require_once 'models/voluntario.php';
        require_once 'config/database.php';

        $voluntarioModel = new Voluntario();

        // Obtener el rol del usuario actual
        $rolUsuario = $this->user_role;

        // Obtener todos los coordinadores (filtrando según el rol del usuario)
        $coordinadores = $voluntarioModel->obtenerCoordinadores($rolUsuario);

        // Variables de permisos
        $puedeEditarDatosBasicos = true;
        $puedeEditarRol = $this->tienePermiso(3);
        $puedeVerVoluntarios = $this->tienePermiso(2);
        $puedeEditarOtros = $this->tienePermiso(3);

        $titulo_pagina = "Coordinadores | Red de Voluntarios";

        $styles = [$this->base_url . 'public/css/p.style.css'];

        $scripts = [
            $this->base_url . 'public/scripts/p.config.js',
            $this->base_url . 'public/scripts/p.script.js'
        ];

        require_once "views/layout/header.php";

        require_once "views/home/coordinadores.php";

        require_once "views/layout/footer.php";
    }

    public function notificaciones()
    {
        $notificacionesFile = __DIR__ . '/NotificacionesController.php';
        if (file_exists($notificacionesFile)) {
            require_once $notificacionesFile;

            if (class_exists('NotificacionesController')) {
                $ctrl = new NotificacionesController();
                if (method_exists($ctrl, 'index')) {
                    $ctrl->index();
                    return;
                }
            }
        }
        header('Location: ' . $this->base_url . 'index.php?controller=home&action=perfil');
        exit();
    }

    public function misSolicitudes()
    {
        require_once 'models/voluntario.php';
        require_once 'config/database.php';

        $voluntarioModel = new Voluntario();
        $usuario_id = $_SESSION['user']['id'];

        // Variables de permisos
        $puedeEditarDatosBasicos = true;
        $puedeEditarRol = $this->tienePermiso(3);
        $puedeVerVoluntarios = $this->tienePermiso(2);
        $puedeEditarOtros = $this->tienePermiso(3);

        $titulo_pagina = "Mis Solicitudes | Red de Voluntarios";

        $styles = [$this->base_url . 'public/css/p.style.css'];

        $scripts = [$this->base_url . 'public/scripts/p.script.js'];

        require_once "views/layout/header.php";

        require_once "views/home/misSolicitudes.php";

        require_once "views/layout/footer.php";
    }
}
