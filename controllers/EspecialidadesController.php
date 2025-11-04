<?php
class EspecialidadesController
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

    /**
     * Página principal de especialidades (vista para el voluntario)
     */
    public function index()
    {
        $titulo_pagina = "Especialidades | Red de Voluntarios";

        $styles = [$this->base_url . 'public/css/e.style.css'];

        $scripts = [$this->base_url . 'public/scripts/e.script.js'];

        require_once "views/layout/header.php";

        require_once "views/home/especialidades.php";

        require_once "views/layout/footer.php";
    }

    /**
     * Método AJAX para obtener las especialidades del voluntario
     * Se llama desde JavaScript
     */
    public function obtenerEspecialidades()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado.']);
            exit();
        }

        try {
            require_once 'models/especialidad.php';
            $especialidadModel = new Especialidad();

            // Si hay un CURP en el parámetro, buscar ese voluntario (modo administrador)
            $curpBuscar = $_GET['curp'] ?? null;

            if ($curpBuscar) {
                // Verificar que el usuario actual sea administrador o coordinador
                $rolUsuario = $_SESSION['user']['rol'] ?? 'Voluntario';
                $esCoordinadorOMas = in_array($rolUsuario, ['Coordinador de Area', 'Administrador', 'Superadministrador']);

                if (!$esCoordinadorOMas) {
                    echo json_encode(['success' => false, 'message' => 'No tienes permisos para ver especialidades de otros voluntarios.']);
                    exit();
                }

                // Obtener ID del voluntario por CURP
                $especialidades = $especialidadModel->verEspecialidadesPorCurp($curpBuscar);
            } else {
                // Modo normal: obtener especialidades del usuario actual
                $voluntarioID = (int)$_SESSION['user']['id'];
                $especialidades = $especialidadModel->verEspecialidades($voluntarioID);
            }

            echo json_encode([
                'success' => true,
                'data' => $especialidades
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener especialidades: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Método AJAX para agregar una especialidad
     */
    public function agregarEspecialidad()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        try {
            // Validar que se recibieron los datos necesarios
            if (!isset($_POST['nombreEspecialidad']) || !isset($_POST['autodescripcion'])) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
                exit();
            }

            // Validar que se subió un archivo
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Debe adjuntar un documento.']);
                exit();
            }

            $voluntarioID = (int)$_SESSION['user']['id'];
            $nombreEspecialidad = trim($_POST['nombreEspecialidad']);
            $autodescripcion = trim($_POST['autodescripcion']);

            // Validar que no tenga ya esta especialidad
            require_once 'models/especialidad.php';
            $especialidadModel = new Especialidad();

            if ($especialidadModel->tieneEspecialidad($voluntarioID, $nombreEspecialidad)) {
                echo json_encode(['success' => false, 'message' => 'Ya tienes esta especialidad registrada.']);
                exit();
            }

            // Procesar archivo
            $archivo = $_FILES['archivo'];
            $extensionesPermitidas = ['pdf'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, $extensionesPermitidas)) {
                echo json_encode(['success' => false, 'message' => 'Solo se permiten archivos en formato PDF.']);
                exit();
            }

            // Limitar tamaño a 5MB
            if ($archivo['size'] > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'El archivo no debe superar los 5MB.']);
                exit();
            }

            // Generar nombre único para el archivo
            $nombreArchivo = 'especialidad_' . $voluntarioID . '_' . time() . '.' . $extension;
            $rutaDestino = 'public/uploads/especialidades/' . $nombreArchivo;
            $rutaCompleta = __DIR__ . '/../' . $rutaDestino;

            // Crear directorio si no existe
            $directorio = dirname($rutaCompleta);
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }

            // Mover archivo
            if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo.']);
                exit();
            }

            // Guardar en base de datos
            $resultado = $especialidadModel->agregarEspecialidad(
                $voluntarioID,
                $nombreEspecialidad,
                $autodescripcion,
                $archivo['name'], // Nombre original del documento
                $rutaDestino      // Ruta donde se guardó
            );

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Especialidad agregada correctamente. Está pendiente de validación.'
                ]);
            } else {
                // Si falla, eliminar el archivo subido
                if (file_exists($rutaCompleta)) {
                    @unlink($rutaCompleta);
                }
                echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
            }
        } catch (Exception $e) {
            // Si hay error, eliminar el archivo si se subió
            if (isset($rutaCompleta) && file_exists($rutaCompleta)) {
                @unlink($rutaCompleta);
            }
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Obtener catálogo de especialidades disponibles (opcional)
     */
    public function obtenerCatalogo()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado.']);
            exit();
        }

        try {
            require_once 'models/especialidad.php';
            $especialidadModel = new Especialidad();

            $especialidades = $especialidadModel->obtenerEspecialidadesDisponibles();

            echo json_encode([
                'success' => true,
                'data' => $especialidades
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener catálogo: ' . $e->getMessage()
            ]);
        }
        exit();
    }
}
