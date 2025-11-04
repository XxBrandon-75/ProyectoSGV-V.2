<?php
class DocumentacionController
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
     * Página principal de documentación
     */
    public function index()
    {
        $titulo_pagina = "Documentación | Red de Voluntarios";

        $styles = [$this->base_url . 'public/css/d.style.css'];

        $scripts = [$this->base_url . 'public/scripts/d.script.js'];

        // Capturar ID de voluntario si se pasa como parámetro (cuando admin ve documentos de un voluntario específico)
        $voluntarioFiltroID = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $verSoloUnVoluntario = false;
        $nombreVoluntario = null;

        // Si hay un ID y el usuario es admin, obtener nombre del voluntario
        if ($voluntarioFiltroID && $this->tienePermiso(3)) {
            require_once 'models/voluntario.php';
            $voluntarioModel = new Voluntario();
            $datosVoluntario = $voluntarioModel->obtenerDatosCompletos($voluntarioFiltroID);

            if ($datosVoluntario) {
                $verSoloUnVoluntario = true;
                $nombreVoluntario = trim($datosVoluntario['Nombres'] . ' ' . $datosVoluntario['ApellidoPaterno'] . ' ' . ($datosVoluntario['ApellidoMaterno'] ?? ''));
            }
        }

        require_once "views/layout/header.php";

        require_once "views/home/documentacion.php";

        require_once "views/layout/footer.php";
    }

    /**
     * Método AJAX para obtener los documentos
     * Admins ven todos los documentos o de un voluntario específico, voluntarios solo los suyos
     */
    public function obtenerDocumentos()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado.']);
            exit();
        }

        try {
            require_once 'models/documentosModels.php';
            $documentoModel = new DocumentoModel();

            $voluntarioID = (int)$_SESSION['user']['id'];
            $esAdmin = $this->tienePermiso(3); // Admin o superior

            // Verificar si se pasa un ID de voluntario específico (para que admin vea solo ese voluntario)
            $voluntarioFiltroID = isset($_GET['voluntarioId']) ? (int)$_GET['voluntarioId'] : null;

            if ($esAdmin) {
                if ($voluntarioFiltroID) {
                    // Admin viendo documentos de un voluntario específico
                    $documentos = $documentoModel->obtenerPorVoluntario($voluntarioFiltroID);
                } else {
                    // Admin ve todos los documentos
                    $documentos = $documentoModel->obtenerTodos();
                }
            } else {
                // Voluntario ve solo sus documentos
                $documentos = $documentoModel->obtenerPorVoluntario($voluntarioID);
            }

            echo json_encode([
                'success' => true,
                'documentos' => $documentos
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener documentos: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Método AJAX para subir un documento
     * Admin sube borradores, Voluntarios suben documentos llenos
     */
    public function subirDocumento()
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
            $voluntarioID = (int)$_SESSION['user']['id'];
            $esAdmin = $this->tienePermiso(3);

            // Determinar si se requiere archivo según el contexto
            $tipoVer = isset($_POST['tipoVer']) ? trim($_POST['tipoVer']) : 'Plantilla';
            $requiereArchivo = true;
            $archivo = null;
            $extension = null;

            if ($esAdmin && $tipoVer === 'Solo Subir') {
                // Admin creando tipo "Solo Subir" - NO requiere archivo
                $requiereArchivo = false;
            }

            // Validar que se subió un archivo solo si es requerido
            if ($requiereArchivo) {
                if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'message' => 'Debe seleccionar un archivo.']);
                    exit();
                }

                $archivo = $_FILES['archivo'];
                $extensionesPermitidas = ['pdf'];
                $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

                if (!in_array($extension, $extensionesPermitidas)) {
                    echo json_encode(['success' => false, 'message' => 'Solo se permiten archivos PDF.']);
                    exit();
                }

                // Validar tipo MIME
                $tipoMimePermitido = 'application/pdf';
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $tipoMime = finfo_file($finfo, $archivo['tmp_name']);
                finfo_close($finfo);

                if ($tipoMime !== $tipoMimePermitido) {
                    echo json_encode(['success' => false, 'message' => 'El archivo debe ser un PDF válido.']);
                    exit();
                }

                // Limitar tamaño a 10MB
                if ($archivo['size'] > 10 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'El archivo no debe superar los 10MB.']);
                    exit();
                }
            }

            if ($esAdmin) {
                // Admin define tipo de documento
                if (!isset($_POST['nombreDocumento'])) {
                    echo json_encode(['success' => false, 'message' => 'Debe especificar el nombre del documento.']);
                    exit();
                }

                $nombreDocumento = trim($_POST['nombreDocumento']);
                $tipoVer = isset($_POST['tipoVer']) ? trim($_POST['tipoVer']) : 'Plantilla';
                $rutaPlantilla = null;

                if ($tipoVer === 'Plantilla') {
                    // Admin sube plantilla
                    $nombreArchivo = 'plantilla_' . time() . '.' . $extension;
                    $rutaDestino = 'public/uploads/documentos/' . $nombreArchivo;
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

                    $rutaPlantilla = '/' . $rutaDestino;
                } else {
                    // Solo Subir - no hay plantilla
                    $tipoVer = 'Solo Subir';
                }

                // Guardar en base de datos
                require_once 'models/documentosModels.php';
                $documentoModel = new DocumentoModel();

                $resultado = $documentoModel->agregarTipoDocumento(
                    $nombreDocumento,
                    $tipoVer,
                    $rutaPlantilla
                );

                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'message' => $tipoVer === 'Plantilla' ? 'Plantilla agregada correctamente.' : 'Tipo de documento agregado correctamente.'
                    ]);
                } else {
                    // Si falla, eliminar el archivo subido
                    if (isset($rutaCompleta) && file_exists($rutaCompleta)) {
                        @unlink($rutaCompleta);
                    }
                    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
                }
            } else {
                // Voluntario sube documento lleno
                if (!isset($_POST['nombreDocumento'])) {
                    echo json_encode(['success' => false, 'message' => 'Debe especificar el documento.']);
                    exit();
                }

                $nombreDocumento = trim($_POST['nombreDocumento']);

                // Generar nombre único para el archivo
                $nombreArchivo = 'expediente_' . $voluntarioID . '_' . time() . '.' . $extension;
                $rutaDestino = 'public/uploads/documentos/' . $nombreArchivo;
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

                // Guardar en base de datos usando procedimiento almacenado
                require_once 'models/documentosModels.php';
                $documentoModel = new DocumentoModel();

                $resultado = $documentoModel->subirDocumentoVoluntario(
                    $voluntarioID,
                    $nombreDocumento,
                    $archivo['name'],
                    '/' . $rutaDestino
                );

                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Documento subido correctamente. Está pendiente de aprobación.'
                    ]);
                } else {
                    // Si falla, eliminar el archivo subido
                    if (file_exists($rutaCompleta)) {
                        @unlink($rutaCompleta);
                    }
                    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
                }
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
     * Descargar un documento
     */
    public function descargarDocumento()
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ' . $this->base_url . 'login.php');
            exit();
        }

        if (!isset($_GET['id'])) {
            echo "Documento no especificado.";
            exit();
        }

        try {
            $voluntarioDocId = (int)$_GET['id'];
            $voluntarioID = (int)$_SESSION['user']['id'];
            $esAdmin = $this->tienePermiso(3);

            require_once 'models/documentosModels.php';
            $documentoModel = new DocumentoModel();

            $documento = $documentoModel->obtenerPorId($voluntarioDocId);

            if (!$documento) {
                echo "Documento no encontrado.";
                exit();
            }

            // Verificar permisos
            if (!$esAdmin && $documento['VoluntarioID'] != $voluntarioID) {
                echo "No tienes permiso para descargar este documento.";
                exit();
            }

            $rutaArchivo = __DIR__ . '/..' . $documento['RutaArchivo'];

            if (!file_exists($rutaArchivo)) {
                echo "Archivo no encontrado en: " . $rutaArchivo;
                exit();
            }

            // Forzar descarga
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($documento['NombreArchivo']) . '"');
            header('Content-Length: ' . filesize($rutaArchivo));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            readfile($rutaArchivo);
            exit();
        } catch (Exception $e) {
            echo "Error al descargar: " . $e->getMessage();
            exit();
        }
    }

    /**
     * Descargar plantilla (documento del catálogo)
     */
    public function descargarPlantilla()
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ' . $this->base_url . 'login.php');
            exit();
        }

        if (!isset($_GET['id'])) {
            echo "Documento no especificado.";
            exit();
        }

        try {
            $documentoId = (int)$_GET['id'];

            require_once __DIR__ . '/../config/Database.php';
            $pdo = Database::getInstance()->getConnection();

            // Obtener la plantilla del catálogo
            $sql = "SELECT DocumentoID, Nombre, RutaPlantilla 
                    FROM dbo.CatDocumentos 
                    WHERE DocumentoID = :documentoId AND TipoVer = 'Plantilla'";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':documentoId', $documentoId, PDO::PARAM_INT);
            $stmt->execute();
            $documento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$documento || !$documento['RutaPlantilla']) {
                echo "Plantilla no encontrada.";
                exit();
            }

            $rutaArchivo = __DIR__ . '/..' . $documento['RutaPlantilla'];

            if (!file_exists($rutaArchivo)) {
                echo "Archivo de plantilla no encontrado.";
                exit();
            }

            // Forzar descarga
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $documento['Nombre'] . '.pdf"');
            header('Content-Length: ' . filesize($rutaArchivo));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            readfile($rutaArchivo);
            exit();
        } catch (Exception $e) {
            echo "Error al descargar plantilla: " . $e->getMessage();
            exit();
        }
    }

    /**
     * Ver un documento en el navegador
     */
    public function verDocumento()
    {
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ' . $this->base_url . 'login.php');
            exit();
        }

        if (!isset($_GET['id'])) {
            echo "Documento no especificado.";
            exit();
        }

        try {
            $voluntarioDocId = (int)$_GET['id'];
            $voluntarioID = (int)$_SESSION['user']['id'];
            $esAdmin = $this->tienePermiso(3);

            require_once 'models/documentosModels.php';
            $documentoModel = new DocumentoModel();

            $documento = $documentoModel->obtenerPorId($voluntarioDocId);

            if (!$documento) {
                echo "Documento no encontrado.";
                exit();
            }

            // Verificar permisos
            if (!$esAdmin && $documento['VoluntarioID'] != $voluntarioID) {
                echo "No tienes permiso para ver este documento.";
                exit();
            }

            $rutaArchivo = __DIR__ . '/..' . $documento['RutaArchivo'];

            if (!file_exists($rutaArchivo)) {
                echo "Archivo no encontrado.";
                exit();
            }

            // Mostrar el archivo inline
            $extension = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));

            if ($extension === 'pdf') {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($documento['NombreArchivo']) . '"');
            } else {
                // Para DOC/DOCX forzar descarga
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($documento['NombreArchivo']) . '"');
            }

            header('Content-Length: ' . filesize($rutaArchivo));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            readfile($rutaArchivo);
            exit();
        } catch (Exception $e) {
            echo "Error al visualizar: " . $e->getMessage();
            exit();
        }
    }

    /**
     * Aprobar un documento (solo admin)
     */
    public function aprobarDocumento()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado.']);
            exit();
        }

        if (!$this->tienePermiso(3)) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para aprobar documentos.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        try {
            if (!isset($_POST['documentoId'])) {
                echo json_encode(['success' => false, 'message' => 'Documento no especificado.']);
                exit();
            }

            $documentoId = (int)$_POST['documentoId'];
            $aprobadoPor = (int)$_SESSION['user']['id'];

            require_once 'models/documentosModels.php';
            $documentoModel = new DocumentoModel();

            $resultado = $documentoModel->aprobar($documentoId, $aprobadoPor);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Documento aprobado correctamente.'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al aprobar el documento.']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Rechazar un documento (solo admin)
     */
    public function rechazarDocumento()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado.']);
            exit();
        }

        if (!$this->tienePermiso(3)) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para rechazar documentos.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        try {
            if (!isset($_POST['documentoId']) || !isset($_POST['motivo'])) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
                exit();
            }

            $documentoId = (int)$_POST['documentoId'];
            $motivo = trim($_POST['motivo']);
            $rechazadoPor = (int)$_SESSION['user']['id'];

            require_once 'models/documentosModels.php';
            $documentoModel = new DocumentoModel();

            $resultado = $documentoModel->rechazar($documentoId, $motivo, $rechazadoPor);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Documento rechazado.'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al rechazar el documento.']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Eliminar un documento/borrador (solo admin)
     */
    public function eliminarDocumento()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado.']);
            exit();
        }

        if (!$this->tienePermiso(3)) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para eliminar documentos.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        try {
            if (!isset($_POST['documentoId'])) {
                echo json_encode(['success' => false, 'message' => 'Documento no especificado.']);
                exit();
            }

            $voluntarioDocId = (int)$_POST['documentoId'];

            require_once 'models/documentosModels.php';
            $documentoModel = new DocumentoModel();

            // Obtener información del documento antes de eliminarlo
            $documento = $documentoModel->obtenerPorId($voluntarioDocId);

            if (!$documento) {
                echo json_encode(['success' => false, 'message' => 'Documento no encontrado.']);
                exit();
            }

            // Eliminar de la base de datos
            $resultado = $documentoModel->eliminar($voluntarioDocId);

            if ($resultado) {
                // Eliminar archivo físico
                $rutaArchivo = __DIR__ . '/..' . $documento['RutaArchivo'];
                if (file_exists($rutaArchivo)) {
                    @unlink($rutaArchivo);
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Documento eliminado correctamente.'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el documento.']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Eliminar tipo de documento del catálogo (solo admin)
     */
    public function eliminarTipoDocumento()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']['id'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado.']);
            exit();
        }

        if (!$this->tienePermiso(3)) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para eliminar tipos de documento.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        try {
            if (!isset($_POST['documentoId'])) {
                echo json_encode(['success' => false, 'message' => 'Documento no especificado.']);
                exit();
            }

            $documentoId = (int)$_POST['documentoId'];

            require_once 'models/documentosModels.php';
            $documentoModel = new DocumentoModel();

            // Eliminar del catálogo
            $resultado = $documentoModel->eliminarTipoDocumento($documentoId);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tipo de documento eliminado del catálogo.'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el tipo de documento.']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit();
    }
}
