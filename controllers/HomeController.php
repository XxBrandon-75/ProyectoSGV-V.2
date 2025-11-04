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
        // Redirigir al controlador dedicado de especialidades
        $especialidadesController = __DIR__ . '/EspecialidadesController.php';

        if (file_exists($especialidadesController)) {
            require_once $especialidadesController;

            if (class_exists('EspecialidadesController')) {
                $ctrl = new EspecialidadesController();
                if (method_exists($ctrl, 'index')) {
                    $ctrl->index();
                    return;
                }
            }
        }

        // Si no existe el controlador, mostrar error
        echo "Error: No se pudo cargar el controlador de especialidades.";
        exit();
    }

    public function tramites()
    {
        // Variables de permisos
        $ver_cont_gest = $this->tienePermiso(2); // Coordinador o superior puede agregar trámites
        $ver_card_edit = $this->tienePermiso(2); // Coordinador o superior puede editar/eliminar

        require_once __DIR__ . '/../models/Tramite.php';
        $tramitesModel = new Tramite();
        $tramites = $tramitesModel->obtenerTramitesActivos();

        $voluntarioID = $_SESSION['user']['id'];

        $titulo_pagina = "Trámites | Red de Voluntarios";

        $styles = [$this->base_url . 'public/css/t.style.css'];

        $scripts = [$this->base_url . 'public/scripts/t.script.js'];

        require_once "views/layout/header.php";

        require_once "views/home/tramites.php";

        require_once "views/layout/footer.php";
    }

    public function documentacion()
    {
        // Redirigir al controlador dedicado de documentación
        $documentacionController = __DIR__ . '/DocumentacionController.php';

        if (file_exists($documentacionController)) {
            require_once $documentacionController;

            if (class_exists('DocumentacionController')) {
                $ctrl = new DocumentacionController();
                if (method_exists($ctrl, 'index')) {
                    $ctrl->index();
                    return;
                }
            }
        }

        // Si no existe el controlador, mostrar error
        echo "Error: No se pudo cargar el controlador de documentación.";
        exit();
    }

    public function perfil()
    {
        require_once 'models/voluntario.php';
        require_once 'config/database.php';
        require_once 'helpers/RolHelper.php';
        require_once 'helpers/CatalogoHelper.php';

        $voluntarioModel = new Voluntario();

        // Verificar si se está viendo el perfil de otro usuario
        $voluntarioID = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_SESSION['user']['id'];
        $esPropioUsuario = ($voluntarioID === (int)$_SESSION['user']['id']);

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

    public function personal()
    {
        // Solo coordinadores o superiores
        if (!$this->tienePermiso(2)) {
            header('Location: ' . $this->base_url . 'index.php?controller=home&action=perfil');
            exit();
        }

        require_once 'models/voluntario.php';
        require_once 'config/database.php';
        require_once 'helpers/CatalogoHelper.php';

        $voluntarioModel = new Voluntario();
        $usuario_id = $_SESSION['user']['id'];

        // Obtener datos del coordinador para saber su delegación
        $datosCoordinador = $voluntarioModel->obtenerDatosCompletos($usuario_id);

        // Obtener datos del usuario actual para el menú lateral (perfil-menu.php)
        $datosUsuario = $datosCoordinador;
        $esPropioUsuario = true; // Siempre es el propio usuario en esta página

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

        // Datos para configuración JavaScript
        $catCiudades = CatalogoHelper::obtenerCiudades();
        $catEstados = CatalogoHelper::obtenerEstados();

        $titulo_pagina = "Voluntarios Personal | Red de Voluntarios";

        $styles = [$this->base_url . 'public/css/p.style.css'];

        $scripts = [
            $this->base_url . 'public/scripts/p.config.js',
            $this->base_url . 'public/scripts/p.script.js'
        ];

        require_once "views/layout/header.php";

        require_once "views/home/personal.php";

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
        require_once 'helpers/CatalogoHelper.php';

        $voluntarioModel = new Voluntario();
        $usuario_id = $_SESSION['user']['id'];

        // Obtener datos del usuario actual para el menú lateral (perfil-menu.php)
        $datosUsuario = $voluntarioModel->obtenerDatosCompletos($usuario_id);
        $esPropioUsuario = true; // Siempre es el propio usuario en esta página

        // Obtener el rol del usuario actual
        $rolUsuario = $this->user_role;

        // Obtener todos los coordinadores (filtrando según el rol del usuario)
        $coordinadores = $voluntarioModel->obtenerCoordinadores($rolUsuario);

        // Variables de permisos
        $puedeEditarDatosBasicos = true;
        $puedeEditarRol = $this->tienePermiso(3);
        $puedeVerVoluntarios = $this->tienePermiso(2);
        $puedeEditarOtros = $this->tienePermiso(3);

        // Datos para configuración JavaScript
        $catCiudades = CatalogoHelper::obtenerCiudades();
        $catEstados = CatalogoHelper::obtenerEstados();

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

    public function bajaVoluntario()
    {
        // Solo administradores o superiores pueden dar de baja
        if (!$this->tienePermiso(3)) {
            header('Location: ' . $this->base_url . 'index.php?controller=home&action=perfil');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voluntario_id'], $_POST['csrf_token'])) {
            require_once 'config/database.php';
            require_once 'helpers/SecurityHelper.php';

            if (!SecurityHelper::validarTokenCSRF($_POST['csrf_token'])) {
                $_SESSION['error'] = 'Token de seguridad inválido.';
                header('Location: ' . $this->base_url . 'index.php?controller=home&action=perfil&id=' . (int)$_POST['voluntario_id']);
                exit();
            }

            $voluntarioID = (int)$_POST['voluntario_id'];
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("EXEC DardeBajaVoluntario @VoluntarioID = :id");
                $stmt->bindParam(':id', $voluntarioID, PDO::PARAM_INT);
                $success = $stmt->execute();

                if ($success) {
                    $_SESSION['success'] = 'El voluntario ha sido dado de baja correctamente.';
                } else {
                    $_SESSION['error'] = 'No se pudo dar de baja al voluntario.';
                }
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error en la base de datos: ' . $e->getMessage();
            }
            header('Location: ' . $this->base_url . 'index.php?controller=home&action=personal');
            exit();
        }
        header('Location: ' . $this->base_url . 'index.php?controller=home&action=perfil');
        exit();
    }

    public function cambiarFotoPerfil()
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
            $error = $_FILES['foto_perfil']['error'];
            if ($error !== UPLOAD_ERR_OK) {
                $msg = 'No se recibió ninguna imagen.';
                if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                    $msg = 'La imagen no debe superar los 2MB.';
                } elseif ($error === UPLOAD_ERR_PARTIAL) {
                    $msg = 'La imagen se subió solo parcialmente.';
                } elseif ($error === UPLOAD_ERR_NO_FILE) {
                    $msg = 'No se seleccionó ninguna imagen.';
                }
                echo json_encode(['success' => false, 'message' => $msg]);
                exit();
            }

            $voluntarioID = (int)$_SESSION['user']['id'];

            // Nombre del archivo simplificado: perfil_VoluntarioID.jpg
            $nombreArchivo = 'perfil_' . $voluntarioID . '.jpg';

            require_once 'models/voluntario.php';
            $voluntarioModel = new Voluntario();

            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            $extension = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));

            if ($_FILES['foto_perfil']['size'] > 2 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'La imagen no debe superar los 2MB.']);
                exit();
            }

            if (!in_array($extension, $permitidas)) {
                echo json_encode(['success' => false, 'message' => 'Formato de imagen no permitido.']);
                exit();
            }

            $info = getimagesize($_FILES['foto_perfil']['tmp_name']);
            if (!$info || $info[0] < 400 || $info[1] < 480) {
                echo json_encode(['success' => false, 'message' => 'La imagen debe tener al menos 400x480 píxeles.']);
                exit();
            }

            $rutaDestino = 'public/img/perfiles/' . $nombreArchivo;
            $rutaCompleta = __DIR__ . '/../' . $rutaDestino;

            // Eliminar foto anterior si existe y no es la default
            if (!empty($_SESSION['user']['FotoPerfil'])) {
                $fotoAnterior = $_SESSION['user']['FotoPerfil'];
                $defaultFoto = $this->base_url . 'public/img/perfiles/default.png';
                if ($fotoAnterior !== $defaultFoto && strpos($fotoAnterior, 'public/img/perfiles/') !== false) {
                    $rutaAnterior = __DIR__ . '/../' . str_replace($this->base_url, '', $fotoAnterior);
                    if (file_exists($rutaAnterior)) {
                        @unlink($rutaAnterior);
                    }
                }
            }

            // Procesar imagen: recortar/redimensionar a 400x480px
            $anchoFinal = 400;
            $altoFinal = 480;
            $imgTmp = null;
            if ($extension === 'jpg' || $extension === 'jpeg') {
                $imgTmp = imagecreatefromjpeg($_FILES['foto_perfil']['tmp_name']);
            } elseif ($extension === 'png') {
                $imgTmp = imagecreatefrompng($_FILES['foto_perfil']['tmp_name']);
            } elseif ($extension === 'webp') {
                $imgTmp = imagecreatefromwebp($_FILES['foto_perfil']['tmp_name']);
            }
            if (!$imgTmp) {
                echo json_encode(['success' => false, 'message' => 'No se pudo procesar la imagen.']);
                exit();
            }
            $anchoOriginal = imagesx($imgTmp);
            $altoOriginal = imagesy($imgTmp);
            $ratioFinal = $anchoFinal / $altoFinal;
            $ratioOriginal = $anchoOriginal / $altoOriginal;
            if ($ratioOriginal > $ratioFinal) {
                $nuevoAlto = $altoOriginal;
                $nuevoAncho = intval($altoOriginal * $ratioFinal);
                $src_x = intval(($anchoOriginal - $nuevoAncho) / 2);
                $src_y = 0;
            } else {
                $nuevoAncho = $anchoOriginal;
                $nuevoAlto = intval($anchoOriginal / $ratioFinal);
                $src_x = 0;
                $src_y = intval(($altoOriginal - $nuevoAlto) / 2);
            }
            $imgRecortada = imagecreatetruecolor($anchoFinal, $altoFinal);
            $blanco = imagecolorallocate($imgRecortada, 255, 255, 255);
            imagefill($imgRecortada, 0, 0, $blanco);
            imagecopyresampled(
                $imgRecortada,
                $imgTmp,
                0,
                0,
                $src_x,
                $src_y,
                $anchoFinal,
                $altoFinal,
                $nuevoAncho,
                $nuevoAlto
            );
            if (imagejpeg($imgRecortada, $rutaCompleta, 90)) {
                $voluntarioModel->actualizarFotoPerfil($voluntarioID, $rutaDestino);
                $_SESSION['success'] = 'Foto de perfil actualizada correctamente.';
                $_SESSION['user']['FotoPerfil'] = $this->base_url . $rutaDestino;

                // Devolver URL con timestamp para evitar caché del navegador
                $urlConTimestamp = $this->base_url . $rutaDestino . '?t=' . time();
                echo json_encode(['success' => true, 'message' => 'Foto de perfil actualizada correctamente.', 'url' => $urlConTimestamp]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo guardar la imagen.']);
            }
            imagedestroy($imgTmp);
            imagedestroy($imgRecortada);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se recibió ninguna imagen.']);
        }
        exit();
    }
}
